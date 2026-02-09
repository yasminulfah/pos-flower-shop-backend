<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'shipping_id',
        'package_id',
        'subtotal',
        'shipping_cost',
        'packaging_cost',
        'greeting_card_price',
        'discount_amount',
        'grand_total',
        'amount_paid',
        'amount_change',
        'greeting_card_note',
        'delivery_at',
        'shipping_address',
        'payment_method',
        'payment_token',
        'reference_id',
        'status',
        'source',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shipping(): BelongsTo
    {
        return $this->belongsTo(Shipping::class);
    }

    public function packaging(): BelongsTo
    {
        return $this->belongsTo(Packaging::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
