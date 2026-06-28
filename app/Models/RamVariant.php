<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RamVariant extends Model
{
    protected $fillable = ['value', 'status'];

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
