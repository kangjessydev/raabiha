<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_active',
        'meta_title',
        'meta_description',
        'published_at',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];
}
