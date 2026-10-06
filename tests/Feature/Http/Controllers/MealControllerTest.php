<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\Meal;
use Tests\TestCase;

final class MealControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Meal::query()->delete();
    }

    protected function tearDown(): void
    {
        Meal::query()->delete();

        parent::tearDown();
    }

    public function test_index_renders_saved_meals(): void
    {
        Meal::factory()->create(['name' => 'Groentelasagne']);

        $response = $this->get(route('meals.index'));

        $response->assertOk()->assertSeeText('Groentelasagne');
    }

    public function test_valid_payload_creates_a_meal(): void
    {
        $response = $this->post(route('meals.store'), $this->mealPayload());

        $meal = Meal::query()->firstOrFail();
        $response->assertRedirectToRoute('meals.index')->assertSessionHas('status', 'Maaltijd aangemaakt.');
        $this->assertSame('Tacoavond', $meal->name);
        $this->assertSame(4, $meal->servings);
        $this->assertSame([
            ['label' => 'Tacoschelpen', 'quantity' => 12, 'unit' => 'g'],
        ], $meal->ingredients);
    }

    public function test_valid_payload_updates_a_meal(): void
    {
        $meal = Meal::factory()->create(['name' => 'Oude naam']);

        $response = $this->put(route('meals.update', $meal), $this->mealPayload([
            'name' => 'Nieuwe naam',
            'servings' => 2,
        ]));

        $updatedMeal = $meal->fresh();
        $response->assertRedirectToRoute('meals.index')->assertSessionHas('status', 'Maaltijd bijgewerkt.');
        $this->assertSame('Nieuwe naam', $updatedMeal->name);
        $this->assertSame(2, $updatedMeal->servings);
    }

    public function test_destroy_removes_the_meal(): void
    {
        $meal = Meal::factory()->create();

        $response = $this->delete(route('meals.destroy', $meal));

        $response->assertRedirectToRoute('meals.index')->assertSessionHas('status', 'Maaltijd verwijderd.');
        $this->assertModelMissing($meal);
    }

    public function test_invalid_payload_returns_dutch_validation_errors(): void
    {
        $response = $this->post(route('meals.store'));

        $response->assertSessionHasErrors([
            'name' => 'Vul een naam voor de maaltijd in.',
            'servings' => 'Vul het aantal porties in.',
            'prep_day' => 'Kies een voorbereidingsdag.',
            'ingredients' => 'Voeg minimaal één ingrediënt toe.',
        ]);
        $this->assertSame(0, Meal::query()->count());
    }

    /** @return array<string, int|string> */
    private function mealPayload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Tacoavond',
            'servings' => 4,
            'prep_day' => 'Friday',
            'ingredient_label_0' => 'Tacoschelpen',
            'ingredient_quantity_0' => 12,
            'ingredient_unit_0' => 'g',
        ], $overrides);
    }
}
