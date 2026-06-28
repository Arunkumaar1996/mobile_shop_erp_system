<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockLedger;
use App\Models\ImeiNumber;
use App\Services\StockService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-products');

        if ($request->ajax()) {
            $query = Stock::with(['warehouse', 'productVariant.product.brand', 'productVariant.product.category', 'productVariant.color', 'productVariant.storageVariant', 'productVariant.ramVariant']);

            if ($warehouseId = $request->input('warehouse_id')) {
                $query->where('warehouse_id', $warehouseId);
            }

            $stocks = $query->get();

            $data = [];
            foreach ($stocks as $s) {
                $pv = $s->productVariant;
                if (!$pv) continue;

                $attributes = [];
                if ($pv->color) $attributes[] = $pv->color->name;
                if ($pv->storageVariant) $attributes[] = $pv->storageVariant->value;
                if ($pv->ramVariant) $attributes[] = $pv->ramVariant->value;

                $nestedData['id'] = $s->id;
                $nestedData['sku'] = e($pv->sku);
                $nestedData['product'] = e($pv->product->name ?? 'N/A') . ' (' . implode(' / ', $attributes) . ')';
                $nestedData['warehouse'] = e($s->warehouse->name ?? 'N/A');
                $nestedData['quantity'] = $s->quantity;
                $nestedData['alert_status'] = ($s->quantity <= $pv->alert_quantity) ? '<span class="badge bg-danger">Low Stock</span>' : '<span class="badge bg-success">Ok</span>';
                
                $data[] = $nestedData;
            }

            return response()->json(['data' => $data]);
        }

        $warehouses = Warehouse::where('status', true)->get();
        return view('inventory.index', compact('warehouses'));
    }

    /* 1. Stock Adjustments */
    public function adjustments(Request $request)
    {
        $this->authorize('edit-products');

        if ($request->ajax()) {
            $adjustments = StockLedger::with(['warehouse', 'productVariant.product', 'user'])
                ->where('reference_type', 'Adjustment')
                ->orderBy('id', 'desc')
                ->get();

            $data = [];
            foreach ($adjustments as $adj) {
                $nestedData['id'] = $adj->id;
                $nestedData['date'] = $adj->created_at->format('Y-m-d H:i');
                $nestedData['warehouse'] = e($adj->warehouse->name ?? 'N/A');
                $nestedData['product'] = e($adj->productVariant->product->name ?? 'N/A') . ' [' . e($adj->productVariant->sku) . ']';
                $nestedData['type'] = ($adj->type === 'in') ? '<span class="badge bg-success">Addition (+)</span>' : '<span class="badge bg-warning">Reduction (-)</span>';
                $nestedData['quantity'] = $adj->quantity;
                $nestedData['old_qty'] = $adj->old_quantity;
                $nestedData['new_qty'] = $adj->new_quantity;
                $nestedData['user'] = e($adj->user->name ?? 'System');
                $data[] = $nestedData;
            }
            return response()->json(['data' => $data]);
        }

        $warehouses = Warehouse::where('status', true)->get();
        $variants = ProductVariant::with(['product', 'color', 'storageVariant', 'ramVariant'])->where('status', true)->get();

        return view('inventory.adjustments', compact('warehouses', 'variants'));
    }

    public function storeAdjustment(Request $request)
    {
        $this->authorize('edit-products');

        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'imeis' => 'nullable|array',
        ]);

        $pv = ProductVariant::with('product')->findOrFail($request->product_variant_id);
        
        // Handle IMEI tracking validation
        $imeis = [];
        if ($pv->product->is_imei_tracked) {
            $rawImeis = $request->input('imeis', []);
            $cleanImeis = array_filter(array_map('trim', $rawImeis));
            
            if (count($cleanImeis) !== intval($request->quantity)) {
                return response()->json(['error' => 'The number of IMEIs must match the adjustment quantity.'], 400);
            }
            $imeis = $cleanImeis;
        }

        try {
            StockService::adjustStock(
                $request->warehouse_id,
                $request->product_variant_id,
                $request->quantity,
                $request->type,
                'Adjustment',
                null,
                $imeis
            );

            ActivityLog::log('Manual Stock Adjustment', 'StockLedger', null, [
                'warehouse_id' => $request->warehouse_id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity,
                'type' => $request->type
            ]);

            return response()->json(['success' => 'Stock adjustment recorded successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /* 2. Stock Transfers */
    public function transfers(Request $request)
    {
        $this->authorize('edit-products');

        if ($request->ajax()) {
            $transfers = StockLedger::with(['warehouse', 'productVariant.product', 'user'])
                ->whereIn('reference_type', ['Transfer In', 'Transfer Out'])
                ->orderBy('id', 'desc')
                ->get();

            // We list movements directly
            $data = [];
            foreach ($transfers as $tr) {
                $nestedData['id'] = $tr->id;
                $nestedData['date'] = $tr->created_at->format('Y-m-d H:i');
                $nestedData['warehouse'] = e($tr->warehouse->name ?? 'N/A');
                $nestedData['product'] = e($tr->productVariant->product->name ?? 'N/A') . ' [' . e($tr->productVariant->sku) . ']';
                $nestedData['type'] = ($tr->type === 'in') ? '<span class="badge bg-info">Received</span>' : '<span class="badge bg-secondary">Sent</span>';
                $nestedData['quantity'] = $tr->quantity;
                $nestedData['reference'] = e($tr->reference_type);
                $nestedData['user'] = e($tr->user->name ?? 'System');
                $data[] = $nestedData;
            }
            return response()->json(['data' => $data]);
        }

        $warehouses = Warehouse::where('status', true)->get();
        $variants = ProductVariant::with(['product', 'color', 'storageVariant', 'ramVariant'])->where('status', true)->get();

        return view('inventory.transfers', compact('warehouses', 'variants'));
    }

    public function storeTransfer(Request $request)
    {
        $this->authorize('edit-products');

        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id|different:to_warehouse_id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'imeis' => 'nullable|array',
        ]);

        $pv = ProductVariant::with('product')->findOrFail($request->product_variant_id);

        $imeis = [];
        if ($pv->product->is_imei_tracked) {
            $rawImeis = $request->input('imeis', []);
            $cleanImeis = array_filter(array_map('trim', $rawImeis));

            if (count($cleanImeis) !== intval($request->quantity)) {
                return response()->json(['error' => 'You must select exactly ' . $request->quantity . ' IMEIs to transfer.'], 400);
            }
            $imeis = $cleanImeis;
        }

        try {
            StockService::transferStock(
                $request->from_warehouse_id,
                $request->to_warehouse_id,
                $request->product_variant_id,
                $request->quantity,
                null,
                $imeis
            );

            ActivityLog::log('Inter-Warehouse Stock Transfer', 'StockLedger', null, [
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'product_variant_id' => $request->product_variant_id,
                'quantity' => $request->quantity
            ]);

            return response()->json(['success' => 'Stock transferred successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /* Helper endpoint: Get IMEIs available in a warehouse */
    public function getAvailableImeis(Request $request)
    {
        $this->authorize('view-products');

        $variantId = $request->input('product_variant_id');
        $warehouseId = $request->input('warehouse_id');

        $imeis = ImeiNumber::where('product_variant_id', $variantId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'available')
            ->get(['id', 'imei']);

        return response()->json($imeis);
    }
}
