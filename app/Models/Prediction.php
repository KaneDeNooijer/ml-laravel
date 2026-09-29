<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

final class Prediction extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'predictions';

    protected $fillable = [
        'scan_event_id',
        'strategy',
        'predicted_category',
        'expected_category',
        'confidence',
        'matches_expected',
        'metadata',
        'estimated',
        'predicted_at',
    ];

    protected function casts(): array
    {
        return [
            'scan_event_id' => 'string',
            'confidence' => 'float',
            'matches_expected' => 'boolean',
            'estimated' => 'boolean',
            'predicted_at' => 'datetime',
        ];
    }
}
