<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImeiNumber extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_variant_id',
        'warehouse_id',
        'imei',
        'status',
        'purchase_invoice_item_id',
        'sales_item_id',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
