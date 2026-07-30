<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosDebtPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'pos_customer_id',
        'pos_session_id',
        'user_id',
        'payment_method',
        'amount_paid',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(PosCustomer::class, 'pos_customer_id');
    }

    public function session()
    {
        return $this->belongsTo(PosSession::class, 'pos_session_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
