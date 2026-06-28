<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockLedger;
use App\Models\ImeiNumber;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Adjust stock count and record ledger movement.
     */
    public static function adjustStock(
        int $warehouseId,
        int $productVariantId,
        int $quantity,
        string $type, // 'in' or 'out'
        string $referenceType, // e.g. 'Opening Stock', 'Purchase', 'Sale', 'Adjustment', 'Transfer'
        ?int $referenceId = null,
        ?array $imeis = null
    ): void {
        DB::transaction(function () use ($warehouseId, $productVariantId, $quantity, $type, $referenceType, $referenceId, $imeis) {
            $stock = Stock::firstOrCreate([
                'warehouse_id' => $warehouseId,
                'product_variant_id' => $productVariantId
            ], [
                'quantity' => 0
            ]);

            $oldQty = $stock->quantity;
            $newQty = $oldQty;

            if ($type === 'in') {
                $newQty = $oldQty + $quantity;
            } elseif ($type === 'out') {
                $newQty = $oldQty - $quantity;
                if ($newQty < 0) {
                    throw new \Exception("Insufficient stock in warehouse for variant ID {$productVariantId}. Current stock: {$oldQty}, requested reduction: {$quantity}");
                }
            }

            $stock->quantity = $newQty;
            $stock->save();

            // Record movement in Ledger
            StockLedger::create([
                'warehouse_id' => $warehouseId,
                'product_variant_id' => $productVariantId,
                'type' => $type,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'old_quantity' => $oldQty,
                'new_quantity' => $newQty,
                'user_id' => auth()->id() ?? 1,
                'created_at' => now(),
            ]);

            // Handle IMEIs if provided
            if ($imeis && count($imeis) > 0) {
                if ($type === 'in') {
                    foreach ($imeis as $imeiStr) {
                        ImeiNumber::updateOrCreate([
                            'imei' => $imeiStr
                        ], [
                            'product_variant_id' => $productVariantId,
                            'warehouse_id' => $warehouseId,
                            'status' => 'available',
                            'purchase_invoice_item_id' => $referenceType === 'Purchase' ? $referenceId : null
                        ]);
                    }
                } elseif ($type === 'out') {
                    foreach ($imeis as $imeiStr) {
                        $imeiObj = ImeiNumber::where('imei', $imeiStr)
                            ->where('product_variant_id', $productVariantId)
                            ->where('warehouse_id' , $warehouseId)
                            ->first();

                        if (!$imeiObj) {
                            throw new \Exception("IMEI number '{$imeiStr}' not found or not available in this warehouse.");
                        }

                        $imeiObj->status = ($referenceType === 'Sale') ? 'sold' : 'transferred';
                        if ($referenceType === 'Sale') {
                            $imeiObj->sales_item_id = $referenceId;
                        }
                        $imeiObj->save();
                    }
                }
            }
        });
    }

    /**
     * Transfer stock between warehouses.
     */
    public static function transferStock(
        int $fromWarehouseId,
        int $toWarehouseId,
        int $productVariantId,
        int $quantity,
        ?int $referenceId = null,
        ?array $imeis = null
    ): void {
        DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $productVariantId, $quantity, $referenceId, $imeis) {
            // 1. Deduct from source warehouse
            self::adjustStock($fromWarehouseId, $productVariantId, $quantity, 'out', 'Transfer Out', $referenceId, $imeis);

            // 2. Adjust target warehouse
            if ($imeis && count($imeis) > 0) {
                foreach ($imeis as $imeiStr) {
                    $imei = ImeiNumber::where('imei', $imeiStr)->first();
                    if ($imei) {
                        $imei->warehouse_id = $toWarehouseId;
                        $imei->status = 'available';
                        $imei->save();
                    }
                }
            }

            self::adjustStock($toWarehouseId, $productVariantId, $quantity, 'in', 'Transfer In', $referenceId, $imeis);
        });
    }
}
