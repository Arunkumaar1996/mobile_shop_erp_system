<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_cart_amount',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_cart_amount' => 'decimal:2',
        'status' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function isValidForAmount(float $amount): bool
    {
        $today = now()->startOfDay();
        return $this->status &&
            $today->gte($this->start_date) &&
            $today->lte($this->end_date) &&
            $amount >= $this->min_cart_amount;
    }
}
