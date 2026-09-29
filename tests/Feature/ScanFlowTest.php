<?php

use App\Services\CategoryMatching\MlCategoryStrategy;
use Illuminate\Support\Facades\Http;

test('the ML strategy uses the internal prediction contract', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        '127.0.0.1:8100/v1/category-predictions' => Http::response([
            'predicted_category' => 'en:rices',
            'expected_category' => 'en:rices',
            'matches_expected' => true,
            'confidence' => 0.87,
            'model' => ['name' => 'open-food-facts-taxonomy-tfidf', 'version' => 'test'],
            'used_fields' => ['product_name'],
            'estimated' => true,
            'predicted_at' => now()->toISOString(),
        ]),
    ]);

    $result = (new MlCategoryStrategy)->match([
        'barcode' => '8710000000001',
        'product_name' => 'Basmati rice',
        'brands' => 'Demo Foods',
        'categories' => ['en:cereals', 'en:rices'],
        'ingredients_text' => 'rice',
    ], 'en:rices');

    expect($result['available'])->toBeTrue()
        ->and($result['predicted_category'])->toBe('en:rices')
        ->and($result['confidence'])->toBe(0.87);
});
