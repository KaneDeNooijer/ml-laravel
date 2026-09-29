<?php

namespace App\Http\Controllers;

use App\Services\OpenFoodFactsClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class IngredientCategoryController extends Controller
{
    public function __invoke(Request $request, OpenFoodFactsClient $openFoodFacts): JsonResponse
    {
        $query = $request->string('q')->trim()->toString();

        if (mb_strlen($query) < 2) {
            return response()->json(['suggestions' => OpenFoodFactsClient::STANDARD_CATEGORIES]);
        }

        try {
            $taxonomySuggestions = $openFoodFacts->suggestCategories($query);

            return response()->json([
                'suggestions' => collect([$query])->merge($taxonomySuggestions)->unique()->values(),
                'taxonomy_match_count' => count($taxonomySuggestions),
                'source' => $taxonomySuggestions === [] ? 'custom' : 'open_food_facts',
            ]);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            $suggestions = collect(OpenFoodFactsClient::STANDARD_CATEGORIES)
                ->filter(fn (string $category): bool => str_contains(mb_strtolower($category), mb_strtolower($query)))
                ->values()
                ->all();

            return response()->json([
                'suggestions' => collect([$query])->merge($suggestions)->unique()->values(),
                'taxonomy_match_count' => 0,
                'source' => 'local_fallback',
            ]);
        }
    }
}
