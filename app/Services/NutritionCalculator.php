<?php

namespace App\Services;

final class NutritionCalculator
{
    /**
     * @var array<string, string>
     */
    private const NUTRIENTS = [
        'energy_kcal' => 'energy-kcal_100g',
        'protein' => 'proteins_100g',
        'carbohydrates' => 'carbohydrates_100g',
        'fat' => 'fat_100g',
        'saturated_fat' => 'saturated-fat_100g',
        'sugars' => 'sugars_100g',
        'salt' => 'salt_100g',
    ];

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public function calculate(array $product, float $quantity, int $servings): array
    {
        $nutriments = $product['nutriments'] ?? [];
        $perHundred = [];
        $total = [];
        $perPortion = [];

        foreach (self::NUTRIENTS as $name => $sourceKey) {
            $value = $this->number($nutriments[$sourceKey] ?? null);
            $perHundred[$name] = $value;
            $total[$name] = $value === null ? null : round($value * ($quantity / 100), 2);
            $perPortion[$name] = $total[$name] === null ? null : round($total[$name] / max(1, $servings), 2);
        }

        return [
            'quantity' => $quantity,
            'servings' => $servings,
            'per_100g' => $perHundred,
            'total' => $total,
            'per_portion' => $perPortion,
            'source' => 'open_food_facts',
        ];
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @param  array<string, mixed>|null  $previous
     * @return array<string, float|null>|null
     */
    public function compare(array $candidate, ?array $previous): ?array
    {
        if ($previous === null) {
            return null;
        }

        return collect(self::NUTRIENTS)
            ->keys()
            ->mapWithKeys(function (string $name) use ($candidate, $previous): array {
                $current = $candidate['per_portion'][$name] ?? null;
                $before = $previous['per_portion'][$name] ?? null;

                return [$name => $current === null || $before === null ? null : round($current - $before, 2)];
            })
            ->all();
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
