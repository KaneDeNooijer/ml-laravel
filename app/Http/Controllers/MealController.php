<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use App\Models\Prediction;
use App\Models\ScanEvent;
use App\Services\OpenFoodFactsClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class MealController extends Controller
{
    /** @var list<string> */
    private const PREP_DAYS = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    public function index(): View
    {
        return view('meals.index', [
            'meals' => Meal::query()->latest('updated_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('meals.create', [
            'meal' => new Meal,
            'standardIngredients' => OpenFoodFactsClient::STANDARD_CATEGORIES,
        ]);
    }

    public function store(Request $request, OpenFoodFactsClient $openFoodFacts): RedirectResponse
    {
        $attributes = $this->mealAttributes($request, $openFoodFacts);
        $attributes['slug'] = Str::slug($attributes['name']).'-'.Str::lower(Str::random(6));
        $attributes['selections'] = [];

        $meal = Meal::query()->create($attributes);

        return redirect()
            ->route('demo', ['meal' => (string) $meal->id])
            ->with('status', 'Maaltijd aangemaakt. Je kunt nu producten scannen.');
    }

    public function edit(Meal $meal): View
    {
        return view('meals.edit', [
            'meal' => $meal,
            'standardIngredients' => OpenFoodFactsClient::STANDARD_CATEGORIES,
        ]);
    }

    public function update(Request $request, Meal $meal, OpenFoodFactsClient $openFoodFacts): RedirectResponse
    {
        $meal->update([
            ...$this->mealAttributes($request, $openFoodFacts),
            'selections' => [],
        ]);

        return redirect()
            ->route('meals.index')
            ->with('status', 'Maaltijd bijgewerkt. Eerdere productkeuzes zijn gewist.');
    }

    public function destroy(Meal $meal): RedirectResponse
    {
        $scanIds = ScanEvent::query()
            ->where('meal_id', (string) $meal->id)
            ->pluck('_id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();

        if ($scanIds !== []) {
            Prediction::query()->whereIn('scan_event_id', $scanIds)->delete();
        }

        ScanEvent::query()->where('meal_id', (string) $meal->id)->delete();
        $meal->delete();

        return redirect()
            ->route('meals.index')
            ->with('status', 'Maaltijd en bijbehorende scans verwijderd.');
    }

    /** @return array{name: string, servings: int, prep_day: string, ingredients: list<array{category: string, label: string, quantity: float, unit: string}>} */
    private function mealAttributes(Request $request, OpenFoodFactsClient $openFoodFacts): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'servings' => ['required', 'integer', 'min:1', 'max:50'],
            'prep_day' => ['required', Rule::in(self::PREP_DAYS)],
            'ingredients' => ['required', 'array', 'min:1', 'max:20'],
            'ingredients.*.label' => ['required', 'string', 'max:100'],
            'ingredients.*.quantity' => ['required', 'numeric', 'min:1', 'max:10000'],
            'ingredients.*.unit' => ['required', Rule::in(['g', 'ml'])],
        ]);

        $labels = collect($validated['ingredients'])
            ->pluck('label')
            ->map(fn (string $label): string => trim($label))
            ->values()
            ->all();
        $canonicalCategories = $this->canonicalCategories($openFoodFacts, $labels);

        $ingredients = collect($validated['ingredients'])
            ->values()
            ->map(fn (array $ingredient, int $index): array => [
                'category' => $canonicalCategories[$index],
                'label' => trim($ingredient['label']),
                'quantity' => (float) $ingredient['quantity'],
                'unit' => $ingredient['unit'],
            ])
            ->all();

        return [
            'name' => $validated['name'],
            'servings' => (int) $validated['servings'],
            'prep_day' => $validated['prep_day'],
            'ingredients' => $ingredients,
        ];
    }

    /**
     * @param  list<string>  $labels
     * @return list<string>
     */
    private function canonicalCategories(OpenFoodFactsClient $openFoodFacts, array $labels): array
    {
        try {
            return $openFoodFacts->canonicalCategories($labels);
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return collect($labels)
                ->map(fn (string $label): string => 'custom:'.Str::slug($label))
                ->all();
        }
    }
}
