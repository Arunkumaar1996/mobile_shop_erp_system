<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color_id',
        'storage_variant_id',
        'ram_variant_id',
        'sku',
        'cost_price',
        'selling_price',
        'alert_quantity',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function storageVariant()
    {
        return $this->belongsTo(StorageVariant::class);
    }

    public function ramVariant()
    {
        return $this->belongsTo(RamVariant::class);
    }

    public function imeiNumbers()
    {
        return $this->hasMany(ImeiNumber::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
