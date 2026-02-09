<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Packaging extends Model
{
    protected $fillable = [
        'packaging_name',
        'base_packaging_cost',
        'packaging_image',
        'packaging_description',
    ];

    public function orders(): HasMany
    {
        return $this->hasmany(Order::class);
    } 
}
