<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = [
        'user_id', 'title', 'recipient_name', 'phone', 
        'full_address', 'province', 'city', 'district', 
        'postal_code', 'is_primary'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
