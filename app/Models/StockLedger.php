<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'warehouse_id',
        'product_variant_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'old_quantity',
        'new_quantity',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'created_at' => 'datetime',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
