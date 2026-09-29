<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

final class ProductSnapshot extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'product_snapshots';

    protected $fillable = [
        'barcode',
        'product_name',
        'brands',
        'quantity',
        'categories',
        'ingredients_text',
        'nutriments',
        'nova_group',
        'image_url',
        'source',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'nova_group' => 'integer',
            'fetched_at' => 'datetime',
        ];
    }
}
