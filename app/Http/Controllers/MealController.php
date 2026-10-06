<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PrepDay;
use App\Http\Requests\SaveMealRequest;
use App\Models\Meal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final readonly class MealController
{
    public function index(): View
    {
        return view('meals.index', [
            'meals' => Meal::query()->latest('updated_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('meals.create', [
            'meal' => new Meal([
                'name' => '',
                'servings' => null,
                'prep_day' => null,
                'ingredients' => [
                    ['label' => '', 'quantity' => null, 'unit' => null],
                ],
            ]),
            'prepDays' => PrepDay::options(),
        ]);
    }

    public function store(SaveMealRequest $request): RedirectResponse
    {
        Meal::query()->create($this->mealAttributes($request));

        return redirect()
            ->route('meals.index')
            ->with('status', __('messages.meals.created'));
    }

    public function edit(Meal $meal): View
    {
        return view('meals.edit', [
            'meal' => $meal,
            'prepDays' => PrepDay::options(),
        ]);
    }

    public function update(SaveMealRequest $request, Meal $meal): RedirectResponse
    {
        $meal->update($this->mealAttributes($request));

        return redirect()
            ->route('meals.index')
            ->with('status', __('messages.meals.updated'));
    }

    public function destroy(Meal $meal): RedirectResponse
    {
        $meal->delete();

        return redirect()
            ->route('meals.index')
            ->with('status', __('messages.meals.deleted'));
    }

    /**
     * @return array{name: string, servings: int, prep_day: string, ingredients: list<array{label: string, quantity: float, unit: string}>}
     */
    private function mealAttributes(SaveMealRequest $request): array
    {
        $validated = $request->validated();

        return [
            'name' => trim($validated['name']),
            'servings' => (int) $validated['servings'],
            'prep_day' => $validated['prep_day'],
            'ingredients' => collect($validated['ingredients'])
                ->map(fn (array $ingredient): array => [
                    'label' => trim($ingredient['label']),
                    'quantity' => (float) $ingredient['quantity'],
                    'unit' => $ingredient['unit'],
                ])
                ->values()
                ->all(),
        ];
    }
}
