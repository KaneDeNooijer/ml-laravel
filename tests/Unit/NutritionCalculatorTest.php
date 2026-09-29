<?php

use App\Services\NutritionCalculator;

test('it scales nutrition to ingredient quantity and portions', function (): void {
    $nutrition = (new NutritionCalculator)->calculate([
        'nutriments' => [
            'energy-kcal_100g' => 120,
            'proteins_100g' => 8,
            'fat_100g' => 4,
            'saturated-fat_100g' => 2,
            'carbohydrates_100g' => 10,
            'sugars_100g' => 6,
            'salt_100g' => 0.2,
        ],
    ], 500, 4);

    expect($nutrition['total']['energy_kcal'])->toBe(600.0)
        ->and($nutrition['per_portion']['energy_kcal'])->toBe(150.0)
        ->and($nutrition['per_portion']['protein'])->toBe(10.0)
        ->and($nutrition['per_portion']['salt'])->toBe(0.25);
});
