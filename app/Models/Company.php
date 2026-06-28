<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'website',
        'address',
        'tax_number',
        'currency',
        'currency_symbol',
        'logo',
    ];
}
