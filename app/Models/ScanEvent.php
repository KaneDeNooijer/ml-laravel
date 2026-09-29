<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

final class ScanEvent extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'scan_events';

    protected $fillable = [
        'meal_id',
        'category',
        'quantity',
        'unit',
        'product_snapshot_id',
        'product',
        'match',
        'nutrition',
        'comparison',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'meal_id' => 'string',
            'product_snapshot_id' => 'string',
            'quantity' => 'float',
        ];
    }
}
