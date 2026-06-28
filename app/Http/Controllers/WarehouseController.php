<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Stock;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-settings'); // Only managers can configure warehouses

        if ($request->ajax()) {
            $warehouses = Warehouse::orderBy('id', 'desc')->get();

            $data = [];
            foreach ($warehouses as $w) {
                $statusChecked = $w->status ? 'checked' : '';
                $statusSwitch = '<div class="form-check form-switch d-inline-block">
                    <input class="form-check-input toggle-status" type="checkbox" data-id="' . $w->id . '" ' . $statusChecked . '>
                </div>';

                $actions = '<div class="btn-group btn-group-sm">
                    <a href="' . route('warehouses.stocks', $w->id) . '" class="btn btn-outline-info" title="View Stock"><i class="bi bi-boxes"></i></a>
                    <button class="btn btn-outline-primary edit-btn" data-id="' . $w->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                if (auth()->user()->hasPermission('delete-products')) {
                    $actions .= '<button class="btn btn-outline-danger delete-btn" data-id="' . $w->id . '" title="Delete"><i class="bi bi-trash"></i></button>';
                }
                $actions .= '</div>';

                $nestedData['id'] = $w->id;
                $nestedData['name'] = e($w->name);
                $nestedData['code'] = e($w->code);
                $nestedData['phone'] = e($w->phone ?? 'N/A');
                $nestedData['address'] = e($w->address ?? 'N/A');
                $nestedData['status'] = $statusSwitch;
                $nestedData['actions'] = $actions;
                $data[] = $nestedData;
            }

            return response()->json(['data' => $data]);
        }

        return view('warehouses.index');
    }

    public function store(Request $request)
    {
        $this->authorize('edit-settings');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'boolean'
        ]);

        $warehouse = Warehouse::create($data);

        ActivityLog::log('Created Warehouse', 'Warehouse', $warehouse->id, [
            'name' => $warehouse->name,
            'code' => $warehouse->code
        ]);

        return response()->json(['success' => 'Warehouse created successfully.']);
    }

    public function show(Warehouse $warehouse)
    {
        $this->authorize('view-settings');
        return response()->json($warehouse);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $this->authorize('edit-settings');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses')->ignore($warehouse->id)],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'status' => 'boolean'
        ]);

        $warehouse->update($data);

        ActivityLog::log('Updated Warehouse', 'Warehouse', $warehouse->id, [
            'name' => $warehouse->name,
            'code' => $warehouse->code
        ]);

        return response()->json(['success' => 'Warehouse updated successfully.']);
    }

    public function destroy(Warehouse $warehouse)
    {
        $this->authorize('delete-products');

        if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
            return response()->json(['error' => 'Cannot delete warehouse while it contains stock.'], 400);
        }

        $warehouse->delete();

        ActivityLog::log('Deleted Warehouse', 'Warehouse', $warehouse->id, [
            'name' => $warehouse->name,
            'code' => $warehouse->code
        ]);

        return response()->json(['success' => 'Warehouse deleted successfully.']);
    }

    public function toggleStatus(Request $request, Warehouse $warehouse)
    {
        $this->authorize('edit-settings');

        $warehouse->status = !$warehouse->status;
        $warehouse->save();

        ActivityLog::log('Toggled Warehouse Status', 'Warehouse', $warehouse->id, [
            'name' => $warehouse->name,
            'status' => $warehouse->status
        ]);

        return response()->json(['success' => 'Warehouse status updated successfully.']);
    }

    /**
     * Display stocks list for a warehouse.
     */
    public function stocks(Request $request, Warehouse $warehouse)
    {
        $this->authorize('view-products');

        if ($request->ajax()) {
            $stocks = Stock::with(['productVariant.product.brand', 'productVariant.product.category', 'productVariant.color', 'productVariant.storageVariant', 'productVariant.ramVariant'])
                ->where('warehouse_id', $warehouse->id)
                ->get();

            $data = [];
            foreach ($stocks as $s) {
                $pv = $s->productVariant;
                if (!$pv) continue;

                $attributes = [];
                if ($pv->color) $attributes[] = $pv->color->name;
                if ($pv->storageVariant) $attributes[] = $pv->storageVariant->value;
                if ($pv->ramVariant) $attributes[] = $pv->ramVariant->value;

                $nestedData['sku'] = e($pv->sku);
                $nestedData['product'] = e($pv->product->name ?? 'N/A') . ' (' . implode(' / ', $attributes) . ')';
                $nestedData['brand'] = e($pv->product->brand->name ?? 'N/A');
                $nestedData['category'] = e($pv->product->category->name ?? 'N/A');
                $nestedData['quantity'] = $s->quantity;
                $data[] = $nestedData;
            }

            return response()->json(['data' => $data]);
        }

        return view('warehouses.stocks', compact('warehouse'));
    }
}
