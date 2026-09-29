<?php

namespace App\Services\CategoryMatching;

use App\Contracts\CategoryMatchStrategy;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final class MlCategoryStrategy implements CategoryMatchStrategy
{
    public function match(array $product, string $expectedCategory): array
    {
        try {
            $response = Http::baseUrl((string) config('services.ml.url'))
                ->acceptJson()
                ->connectTimeout(1)
                ->timeout(4)
                ->post('/v1/category-predictions', [
                    'expected_category' => $expectedCategory,
                    'product' => [
                        'barcode' => $product['barcode'],
                        'product_name' => $product['product_name'],
                        'brands' => $product['brands'],
                        'categories' => $product['categories'],
                        'ingredients_text' => $product['ingredients_text'],
                    ],
                ])
                ->throw();

            return array_merge($response->json(), [
                'strategy' => 'ml-service',
                'available' => true,
            ]);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return [
                'strategy' => 'ml-service',
                'available' => false,
                'predicted_category' => null,
                'expected_category' => $expectedCategory,
                'matches_expected' => false,
                'confidence' => 0.0,
                'estimated' => true,
                'error' => 'De ML-service is niet beschikbaar; de baseline wordt gebruikt.',
            ];
        }
    }
}
