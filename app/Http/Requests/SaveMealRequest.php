<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PrepDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('ingredients')) {
            return;
        }

        $ingredientIndexes = collect(array_keys($this->all()))
            ->map(fn (string $key): ?int => preg_match('/^ingredient_label_(\d+)$/', $key, $matches)
                ? (int) $matches[1]
                : null)
            ->filter(fn (?int $index): bool => $index !== null)
            ->sort()
            ->values();

        $this->merge([
            'ingredients' => $ingredientIndexes
                ->map(fn (int $index): array => [
                    'label' => $this->input("ingredient_label_$index"),
                    'quantity' => $this->input("ingredient_quantity_$index"),
                    'unit' => $this->input("ingredient_unit_$index"),
                ])
                ->all(),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'servings' => ['required', 'integer', 'min:1', 'max:50'],
            'prep_day' => ['required', Rule::enum(PrepDay::class)],
            'ingredients' => ['required', 'array', 'min:1', 'max:20'],
            'ingredients.*.label' => ['required', 'string', 'max:100'],
            'ingredients.*.quantity' => ['required', 'numeric', 'min:1', 'max:10000'],
            'ingredients.*.unit' => ['required', Rule::in(['g', 'ml'])],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => __('messages.validation.meal_name_required'),
            'servings.required' => __('messages.validation.servings_required'),
            'servings.integer' => __('messages.validation.servings_integer'),
            'prep_day.required' => __('messages.validation.prep_day_required'),
            'ingredients.required' => __('messages.validation.ingredients_required'),
            'ingredients.min' => __('messages.validation.ingredients_required'),
            'ingredients.*.label.required' => __('messages.validation.ingredient_label_required'),
            'ingredients.*.quantity.required' => __('messages.validation.ingredient_quantity_required'),
            'ingredients.*.quantity.numeric' => __('messages.validation.ingredient_quantity_numeric'),
            'ingredients.*.unit.required' => __('messages.validation.ingredient_unit_required'),
        ];
    }
}
