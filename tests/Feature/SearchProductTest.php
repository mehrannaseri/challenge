<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SearchProductTest extends TestCase
{
    use DatabaseMigrations;

    protected $defaultCurrency;
    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeaders([
            'Content-type' => 'application/json',
            'Accept' => 'application/json'
        ]);

        $this->defaultCurrency = config('setting.defaultCurrency');
    }

    /**
     *
     * @return void
     */
    public function test_search_unavailable_products_without_integration_and_date_filter()
    {
        Product::factory()->create();

        $response = $this->get('api/search');

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    public function test_search_products_with_wrong_format_startDate()
    {

        Product::factory()->create();

        $response = $this->get('api/search?startDate=hello');
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('startDate');
    }

    public function test_search_products_with_wrong_format_endDate()
    {

        Product::factory()->create();

        $response = $this->get('api/search?endDate=hello');
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('endDate');
    }

    public function test_search_products_endDate_less_than_startDate()
    {

        Product::factory()->create();
        $startDate = Carbon::now();
        $endDate = $startDate->subDays(4);

        $response = $this->get('api/search?startDate'.$startDate->format('Y-m-d').'&endDate='.$endDate->format('Y-m-d'));
        $response->assertStatus(422);
        $response->assertJsonValidationErrorFor('endDate');
    }


    public function test_search_products_with_availability()
    {

        Availability::factory()->create();

        $product = Product::withMin('availabilities', 'price')->first();

        $response = $this->get('api/search');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => $product->name,
            'minimumPrice' => $product->availabilities_min_price." ". $this->defaultCurrency,
            'thumbnail' => $product->thumbnail
        ]);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('availabilities', 1);
    }

    public function test_search_products_with_multiple_availability()
    {

        $product = Product::factory()->create();

        Availability::factory(10)->create(['product_id' => $product->id]);

        $product->loadMin('availabilities', 'price');

        $response = $this->get('api/search');
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'title' => $product->name,
            'minimumPrice' => $product->availabilities_min_price." ". $this->defaultCurrency,
            'thumbnail' => $product->thumbnail
        ]);

        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('availabilities', 10);
    }




}
