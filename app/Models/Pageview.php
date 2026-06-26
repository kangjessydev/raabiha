<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pageview extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'ip_address',
        'url',
        'page_type',
        'model_id',
        'title',
        'user_agent',
    ];
}
