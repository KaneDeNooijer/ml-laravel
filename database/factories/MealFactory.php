<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PrepDay;
use App\Models\Meal;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Meal> */
final class MealFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->randomElement(['Pasta pesto', 'Rijstschotel', 'Tacoavond', 'Groentecurry']);

        return [
            'name' => $name,
            'servings' => fake()->numberBetween(2, 6),
            'prep_day' => fake()->randomElement(PrepDay::cases())->value,
            'ingredients' => [
                ['label' => 'Tomaten', 'quantity' => 400.0, 'unit' => 'g'],
                ['label' => 'Uien', 'quantity' => 150.0, 'unit' => 'g'],
            ],
        ];
    }
}
