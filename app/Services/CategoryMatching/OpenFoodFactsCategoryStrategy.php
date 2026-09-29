<?php

namespace App\Services\CategoryMatching;

use App\Contracts\CategoryMatchStrategy;
use Illuminate\Support\Str;

final class OpenFoodFactsCategoryStrategy implements CategoryMatchStrategy
{
    /**
     * @var array<string, list<string>>
     */
    private const KEYWORDS = [
        'yoghurt' => ['yoghurt', 'yogurt', 'skyr', 'kwark', 'fromage blanc'],
        'milk' => ['milk', 'melk', 'lait', 'latte'],
        'cheese' => ['cheese', 'kaas', 'fromage', 'gouda', 'cheddar', 'mozzarella', 'brie'],
        'butter' => ['butter', 'boter', 'beurre', 'ghee'],
    ];

    public function match(array $product, string $expectedCategory): array
    {
        $productCategories = collect($product['categories'] ?? [])
            ->filter(fn (mixed $category): bool => is_string($category))
            ->values();

        if ($productCategories->contains($expectedCategory)) {
            return [
                'strategy' => 'open-food-facts-taxonomy',
                'available' => true,
                'predicted_category' => $expectedCategory,
                'expected_category' => $expectedCategory,
                'matches_expected' => true,
                'confidence' => 0.98,
                'estimated' => false,
                'used_fields' => ['categories'],
            ];
        }

        $haystack = Str::lower(implode(' ', [
            $product['product_name'] ?? '',
            $product['brands'] ?? '',
            implode(' ', $product['categories'] ?? []),
            $product['ingredients_text'] ?? '',
        ]));

        $scores = collect(self::KEYWORDS)->mapWithKeys(function (array $keywords, string $category) use ($haystack): array {
            $hits = collect($keywords)->filter(fn (string $keyword): bool => Str::contains($haystack, $keyword))->count();

            return [$category => $hits];
        })->sortDesc();

        $predictedCategory = (string) ($scores->keys()->first() ?? 'unknown');
        $hits = (int) ($scores->first() ?? 0);

        if ($hits === 0) {
            $predictedCategory = (string) ($productCategories->last() ?? 'unknown');
        }

        return [
            'strategy' => 'open-food-facts-keywords',
            'available' => true,
            'predicted_category' => $predictedCategory,
            'expected_category' => $expectedCategory,
            'matches_expected' => $predictedCategory === $expectedCategory,
            'confidence' => $hits === 0 ? 0.0 : min(0.95, 0.55 + ($hits * 0.10)),
            'estimated' => true,
            'used_fields' => ['product_name', 'brands', 'categories', 'ingredients_text'],
        ];
    }
}
