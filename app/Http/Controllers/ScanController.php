<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\ProductSnapshot;
use App\Models\ScanEvent;
use App\Repositories\DemoRepository;
use App\Services\CategoryMatchService;
use App\Services\NutritionCalculator;
use App\Services\OpenFoodFactsClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class ScanController extends Controller
{
    public function store(
        Request $request,
        DemoRepository $repository,
        OpenFoodFactsClient $products,
        CategoryMatchService $matcher,
        NutritionCalculator $nutritionCalculator,
    ): JsonResponse {
        $input = $request->validate([
            'meal_id' => ['required', 'string'],
            'barcode' => ['required', 'regex:/^[0-9]{8,14}$/'],
            'category' => ['required', 'string', 'max:150'],
        ]);

        try {
            $meal = $repository->meal($input['meal_id']);
            $ingredient = $repository->ingredient($meal, $input['category']);
            $previous = $repository->previousScan($meal, $input['category']);
            $product = $products->find($input['barcode']);
            $snapshot = ProductSnapshot::query()->updateOrCreate(
                ['barcode' => $product['barcode']],
                $product,
            );
            $match = $matcher->match($product, $input['category']);
            $nutrition = $nutritionCalculator->calculate(
                $product,
                (float) $ingredient['quantity'],
                (int) $meal->servings,
            );
            $comparison = $nutritionCalculator->compare($nutrition, $previous?->nutrition);
            $scan = ScanEvent::query()->create([
                'meal_id' => (string) $meal->id,
                'category' => $input['category'],
                'quantity' => (float) $ingredient['quantity'],
                'unit' => $ingredient['unit'],
                'product_snapshot_id' => (string) $snapshot->id,
                'product' => $product,
                'match' => $match,
                'nutrition' => $nutrition,
                'comparison' => $comparison,
                'status' => 'candidate',
            ]);

            foreach (['baseline', 'ml'] as $strategy) {
                $result = $match[$strategy];
                Prediction::query()->create([
                    'scan_event_id' => (string) $scan->id,
                    'strategy' => $result['strategy'],
                    'predicted_category' => $result['predicted_category'],
                    'expected_category' => $input['category'],
                    'confidence' => $result['confidence'],
                    'matches_expected' => $result['matches_expected'],
                    'metadata' => $result['model'] ?? ['used_fields' => $result['used_fields'] ?? []],
                    'estimated' => true,
                    'predicted_at' => $result['predicted_at'] ?? now(),
                ]);
            }

            return response()->json([
                'scan_id' => (string) $scan->id,
                'product' => $product,
                'match' => $match,
                'nutrition' => $nutrition,
                'comparison' => $comparison,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], $exception->getCode() ?: 502);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return response()->json(['message' => 'Open Food Facts is momenteel niet bereikbaar.'], 503);
        }
    }

    public function accept(string $scan, DemoRepository $repository): JsonResponse
    {
        $scanEvent = ScanEvent::query()->findOrFail($scan);
        $meal = $repository->meal($scanEvent->meal_id);

        return response()->json([
            'message' => 'Product is toegevoegd aan de maaltijd.',
            'demo' => $repository->state($repository->accept($meal, $scanEvent)),
        ]);
    }
}
