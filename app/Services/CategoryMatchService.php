<?php

namespace App\Services;

use App\Services\CategoryMatching\MlCategoryStrategy;
use App\Services\CategoryMatching\OpenFoodFactsCategoryStrategy;

final class CategoryMatchService
{
    public function __construct(
        private OpenFoodFactsCategoryStrategy $baseline,
        private MlCategoryStrategy $ml,
    ) {}

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public function match(array $product, string $expectedCategory): array
    {
        $baseline = $this->baseline->match($product, $expectedCategory);
        $ml = $this->ml->match($product, $expectedCategory);
        $primary = $ml['available'] ? $ml : $baseline;
        $threshold = (float) config('services.ml.confidence_threshold');

        $status = match (true) {
            (float) $primary['confidence'] < $threshold => 'uncertain',
            (bool) $primary['matches_expected'] => 'matched',
            default => 'mismatched',
        };

        return [
            'status' => $status,
            'threshold' => $threshold,
            'primary' => $primary,
            'baseline' => $baseline,
            'ml' => $ml,
        ];
    }
}
