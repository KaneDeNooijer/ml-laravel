<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class OpenFoodFactsClient
{
    /** @var list<string> */
    public const STANDARD_CATEGORIES = [
        'Aardappelen',
        'Appels',
        'Bananen',
        'Boter',
        'Brood',
        'Champignons',
        'Eieren',
        'Feta',
        'Gehakt',
        'Havermout',
        'Kaas',
        'Kikkererwten',
        'Kip',
        'Komkommers',
        'Linzen',
        'Melk',
        'Mozzarella',
        'Noten',
        'Olijfolie',
        'Paprika',
        'Pasta',
        'Pindakaas',
        'Rijst',
        'Room',
        'Spinazie',
        'Tofu',
        'Tomaten',
        'Tonijn',
        'Vis',
        'Yoghurt',
    ];

    private const FIELDS = [
        'code',
        'product_name',
        'brands',
        'quantity',
        'categories_tags',
        'ingredients_text',
        'nutriments',
        'nova_group',
        'nova_groups',
        'image_front_small_url',
    ];

    /** @return array<string, mixed> */
    public function find(string $barcode): array
    {
        $response = $this->request()
            ->get('/api/v3/product/'.$barcode, [
                'fields' => implode(',', self::FIELDS),
            ]);

        if ($response->notFound() || $response->json('status') === 0) {
            throw new RuntimeException('Dit product staat niet in Open Food Facts.', 404);
        }

        $response->throw();
        $product = $response->json('product');

        if (! is_array($product)) {
            throw new RuntimeException('Open Food Facts gaf geen bruikbaar product terug.', 502);
        }

        return [
            'barcode' => (string) ($product['code'] ?? $barcode),
            'product_name' => (string) ($product['product_name'] ?? 'Onbekend product'),
            'brands' => (string) ($product['brands'] ?? ''),
            'quantity' => (string) ($product['quantity'] ?? ''),
            'categories' => array_values($product['categories_tags'] ?? []),
            'ingredients_text' => (string) ($product['ingredients_text'] ?? ''),
            'nutriments' => is_array($product['nutriments'] ?? null) ? $product['nutriments'] : [],
            'nova_group' => $this->novaGroup($product),
            'image_url' => (string) ($product['image_front_small_url'] ?? ''),
            'source' => 'open_food_facts',
            'fetched_at' => now()->toISOString(),
        ];
    }

    /** @return list<string> */
    public function suggestCategories(string $query, int $limit = 20): array
    {
        $cacheKey = 'open-food-facts.category-suggestions.'.sha1(Str::lower($query));

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($query, $limit): array {
            return collect(['nl', 'en'])
                ->flatMap(function (string $language) use ($query, $limit): array {
                    try {
                        $response = $this->request()
                            ->get('/api/v3/taxonomy_suggestions', [
                                'tagtype' => 'categories',
                                'lc' => $language,
                                'string' => $query,
                                'limit' => $limit,
                            ])
                            ->throw();

                        return $response->json('suggestions', []);
                    } catch (ConnectionException|RequestException) {
                        return [];
                    }
                })
                ->filter(fn (mixed $suggestion): bool => is_string($suggestion))
                ->unique(fn (string $suggestion): string => Str::lower($suggestion))
                ->take($limit)
                ->values()
                ->all();
        });
    }

    /**
     * @param  list<string>  $labels
     * @return list<string>
     */
    public function canonicalCategories(array $labels): array
    {
        $categories = $this->canonicalizeTags($labels, 'nl');
        $unresolvedIndexes = collect($categories)
            ->keys()
            ->filter(fn (int $index): bool => ! ($categories[$index]['exists_in_taxonomy'] ?? false))
            ->values();

        if ($unresolvedIndexes->isNotEmpty()) {
            $englishCategories = $this->canonicalizeTags(
                $unresolvedIndexes->map(fn (int $index): string => $labels[$index])->all(),
                'en',
            );

            $unresolvedIndexes->each(function (int $originalIndex, int $englishIndex) use (&$categories, $englishCategories): void {
                if ($englishCategories[$englishIndex]['exists_in_taxonomy'] ?? false) {
                    $categories[$originalIndex] = $englishCategories[$englishIndex];
                }
            });
        }

        return collect($labels)
            ->map(fn (string $label, int $index): string => ($categories[$index]['exists_in_taxonomy'] ?? false)
                ? (string) $categories[$index]['tag']
                : 'custom:'.Str::slug($label))
            ->all();
    }

    /**
     * @param  list<string>  $labels
     * @return list<array{tag: string, exists_in_taxonomy: bool}>
     */
    private function canonicalizeTags(array $labels, string $language): array
    {
        $response = $this->request()
            ->get('/api/v3/taxonomy_canonicalize_tags', [
                'tagtype' => 'categories',
                'lc' => $language,
                'local_tags_list' => implode(',', $labels),
            ])
            ->throw();

        $categories = $response->json('canonical_tags', []);

        if (! is_array($categories) || count($categories) !== count($labels)) {
            throw new RuntimeException('Open Food Facts kon niet alle ingrediëntcategorieën verwerken.', 502);
        }

        return $categories;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl((string) config('services.open_food_facts.url'))
            ->withUserAgent((string) config('services.open_food_facts.user_agent'))
            ->acceptJson()
            ->connectTimeout(3)
            ->timeout(8);
    }

    /** @param array<string, mixed> $product */
    private function novaGroup(array $product): ?int
    {
        $value = $product['nova_group'] ?? $product['nova_groups'] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }
}
