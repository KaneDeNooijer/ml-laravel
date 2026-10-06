<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PrepDay;
use Database\Factories\MealFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

#[Connection('mongodb')]
#[Table('meals')]
#[UseFactory(MealFactory::class)]
final class Meal extends Model
{
    /** @use HasFactory<MealFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'servings' => 'integer',
            'prep_day' => PrepDay::class,
            'ingredients' => 'array',
        ];
    }
}
