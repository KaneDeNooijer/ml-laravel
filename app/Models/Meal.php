<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

final class Meal extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'meals';

    protected $fillable = [
        'slug',
        'name',
        'servings',
        'prep_day',
        'ingredients',
        'selections',
    ];

    protected function casts(): array
    {
        return [
            'servings' => 'integer',
        ];
    }
}
