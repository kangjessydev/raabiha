<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'link',
        'placement',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($banner) {
            if ($banner->is_active) {
                // Deactivate all other banners
                static::where('id', '!=', $banner->id ?? 0)->update(['is_active' => false]);
            }
        });
    }
}
