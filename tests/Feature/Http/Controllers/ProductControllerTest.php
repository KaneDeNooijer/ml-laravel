<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ProductControllerTest extends TestCase
{
    private const string PYTHON_URL = 'http://python.test';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.python_api.url', self::PYTHON_URL);
        Http::preventStrayRequests();
    }

    public function test_shows_the_nova_group_the_model_estimated(): void
    {
        Http::fake([self::PYTHON_URL.'/products/87100002' => Http::response($this->product([
            'group' => 4,
            'estimated' => true,
            'model' => 'k_nearest_neighbours',
            'confidence' => 0.87,
            'reasons' => ['ingredient:en:flavouring', 'additives:3'],
            'trained_on' => 59,
            'category' => 'en:yogurts',
        ]))]);

        $response = $this->get(route('products.show', '87100002'));

        $response->assertOk()
            ->assertSeeText('Bosvruchtenyoghurt')
            ->assertSeeText('Groep 4')
            ->assertSeeText('Geschat door ons model')
            ->assertSeeText('Het model is hier 87% zeker van.')
            ->assertSeeText('59 producten uit dezelfde categorie (en:yogurts)')
            ->assertSeeText('ingredient:en:flavouring');
    }

    public function test_shows_a_measured_nova_group_without_estimate_details(): void
    {
        Http::fake([self::PYTHON_URL.'/products/87100002' => Http::response($this->product([
            'group' => 1, 'estimated' => false, 'model' => null, 'confidence' => null,
            'reasons' => [], 'trained_on' => 0, 'category' => null,
        ]))]);

        $response = $this->get(route('products.show', '87100002'));

        $response->assertOk()
            ->assertSeeText('Groep 1')
            ->assertSeeText('Deze groep komt uit Open Food Facts.')
            ->assertDontSeeText('Geschat door ons model');
    }

    public function test_shows_an_error_when_the_product_does_not_exist(): void
    {
        Http::fake([self::PYTHON_URL.'/products/87100009' => Http::response(['detail' => 'not found'], 404)]);

        $this->get(route('products.show', '87100009'))
            ->assertOk()
            ->assertSeeText('Dit product staat niet in Open Food Facts.');
    }

    public function test_shows_an_error_when_the_python_service_fails(): void
    {
        Http::fake([self::PYTHON_URL.'/products/87100002' => Http::response(['detail' => 'down'], 502)]);

        $this->get(route('products.show', '87100002'))
            ->assertOk()
            ->assertSeeText('De productgegevens konden niet worden opgehaald.');
    }

    public function test_rejects_a_value_that_is_not_a_barcode(): void
    {
        $this->get('/products/abc')->assertNotFound();
    }

    /**
     * Mirrors the Python `/products/{barcode}` response.
     *
     * @param  array<string, mixed>  $nova
     * @return array<string, mixed>
     */
    private function product(array $nova): array
    {
        return [
            'barcode' => '87100002',
            'name' => 'Bosvruchtenyoghurt',
            'brands' => 'Demo Zuivel',
            'categories' => ['en:dairies', 'en:yogurts'],
            'nova' => $nova,
        ];
    }
}
