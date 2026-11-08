<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * The only gateway from Laravel to the Python ML service (Adapter): the rest of
 * the app works with the array below and never with the HTTP API itself.
 *
 * @phpstan-type Nova array{group: int|null, estimated: bool, model: string|null, confidence: float|null, reasons: list<string>, trained_on: int, category: string|null}
 * @phpstan-type Product array{barcode: string, name: string, brands: string, categories: list<string>, nova: Nova}
 */
final readonly class PythonService
{
    public function __construct(
        #[Config('services.python_api.url')]
        private string $baseUrl,
        #[Config('services.python_api.timeout')]
        private int $timeout,
    ) {}

    /**
     * A product with its NOVA group, estimated by the ML model when Open Food Facts lacks it.
     *
     * @return Product|null Null when the product does not exist.
     *
     * @throws PythonServiceException
     */
    public function findProduct(string $barcode): ?array
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout($this->timeout)
                ->acceptJson()
                ->get('/products/'.$barcode);
        } catch (ConnectionException $exception) {
            throw PythonServiceException::unreachable($exception);
        }

        if ($response->notFound()) {
            return null;
        }

        if ($response->failed()) {
            throw PythonServiceException::failedResponse($response->status());
        }

        $nova = (array) $response->json('nova', []);

        return [
            'barcode' => (string) $response->json('barcode'),
            'name' => (string) $response->json('name', ''),
            'brands' => (string) $response->json('brands', ''),
            'categories' => array_values(array_filter((array) $response->json('categories', []), 'is_string')),
            'nova' => [
                'group' => in_array($nova['group'] ?? null, [1, 2, 3, 4], true) ? $nova['group'] : null,
                'estimated' => (bool) ($nova['estimated'] ?? false),
                'model' => is_string($nova['model'] ?? null) ? $nova['model'] : null,
                'confidence' => is_numeric($nova['confidence'] ?? null) ? (float) $nova['confidence'] : null,
                'reasons' => array_values(array_filter((array) ($nova['reasons'] ?? []), 'is_string')),
                'trained_on' => (int) ($nova['trained_on'] ?? 0),
                'category' => is_string($nova['category'] ?? null) ? $nova['category'] : null,
            ],
        ];
    }
}
