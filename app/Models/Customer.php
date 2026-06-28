<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'wallet_balance',
        'loyalty_points',
        'status',
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'loyalty_points' => 'integer',
        'status' => 'boolean',
    ];
}
