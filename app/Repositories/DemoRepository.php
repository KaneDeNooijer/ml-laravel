<?php

namespace App\Repositories;

use App\Models\Meal;
use App\Models\ScanEvent;
use App\Services\PrepScoreCalculator;

final class DemoRepository
{
    public function __construct(private PrepScoreCalculator $prepScoreCalculator) {}

    public function currentMeal(?string $mealId = null): Meal
    {
        if ($mealId !== null) {
            return Meal::query()->findOrFail($mealId);
        }

        return Meal::query()->firstOrCreate(
            ['slug' => 'dairy-breakfast-prep'],
            [
                'name' => 'Dairy breakfast prep',
                'servings' => 4,
                'prep_day' => 'Sunday',
                'ingredients' => [
                    ['category' => 'yoghurt', 'label' => 'Yoghurt', 'quantity' => 500, 'unit' => 'g'],
                    ['category' => 'milk', 'label' => 'Milk', 'quantity' => 400, 'unit' => 'g'],
                    ['category' => 'cheese', 'label' => 'Cheese', 'quantity' => 160, 'unit' => 'g'],
                    ['category' => 'butter', 'label' => 'Butter', 'quantity' => 40, 'unit' => 'g'],
                ],
                'selections' => [],
            ],
        );
    }

    public function meal(string $mealId): Meal
    {
        return Meal::query()->findOrFail($mealId);
    }

    /** @return array<string, mixed> */
    public function ingredient(Meal $meal, string $category): array
    {
        $ingredient = collect($meal->ingredients)->firstWhere('category', $category);

        abort_if($ingredient === null, 422, 'Unknown ingredient category.');

        return (array) $ingredient;
    }

    public function previousScan(Meal $meal, string $category): ?ScanEvent
    {
        return ScanEvent::query()
            ->where('meal_id', (string) $meal->id)
            ->where('category', $category)
            ->latest()
            ->first();
    }

    public function accept(Meal $meal, ScanEvent $scan): Meal
    {
        $selections = collect($meal->selections ?? [])
            ->reject(fn (mixed $selection): bool => ($selection['category'] ?? null) === $scan->category)
            ->push([
                'category' => $scan->category,
                'scan_event_id' => (string) $scan->id,
                'product' => $scan->product,
                'nutrition' => $scan->nutrition,
                'accepted_at' => now()->toISOString(),
            ])
            ->values()
            ->all();

        $meal->selections = $selections;
        $meal->save();
        $scan->status = 'accepted';
        $scan->save();

        return $meal->refresh();
    }

    /** @return array<string, mixed> */
    public function state(Meal $meal): array
    {
        $nutrientKeys = ['energy_kcal', 'protein', 'carbohydrates', 'fat', 'saturated_fat', 'sugars', 'salt'];
        $selections = collect($meal->selections ?? []);
        $total = collect($nutrientKeys)->mapWithKeys(fn (string $key): array => [
            $key => round($selections->sum(fn (mixed $selection): float => (float) ($selection['nutrition']['total'][$key] ?? 0)), 2),
        ])->all();
        $perPortion = collect($total)->map(fn (float $value): float => round($value / max(1, $meal->servings), 2))->all();
        $novaGroup = $selections->pluck('product.nova_group')->filter()->max();

        return [
            'meal_id' => (string) $meal->id,
            'name' => $meal->name,
            'servings' => $meal->servings,
            'prep_day' => $meal->prep_day,
            'ingredients' => $meal->ingredients,
            'selections' => $meal->selections ?? [],
            'nutrition' => ['total' => $total, 'per_portion' => $perPortion],
            'prep_score' => $this->prepScoreCalculator->calculate($perPortion, $novaGroup ? (int) $novaGroup : null),
        ];
    }
}
