<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosHeldCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_id',
        'cashier_name',
        'user_id',
        'customer_name',
        'customer_phone',
        'cart_data',
        'total',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
