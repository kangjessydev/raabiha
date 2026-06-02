<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeOptionProductVariant extends Model
{
    use HasFactory;

    protected $table = 'attribute_option_product_variant';
    public $timestamps = false;

    protected $fillable = [
        'product_variant_id',
        'attribute_option_id',
    ];

    public function productVariant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function attributeOption(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AttributeOption::class);
    }
}
