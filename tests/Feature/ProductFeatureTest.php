<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Product;
use App\Models\ProductType;

class ProductFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test can see products page
     */
    public function test_can_see_products_page()
    {
        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('📖 Products and types');
    }

    /**
     * Test can fetch products for datatable
     */
    public function test_can_fetch_products_for_datatable()
    {
        $productType = ProductType::factory()->create();
        $products = Product::factory(5)->create(['product_type_id' => $productType->id]);

        $response = $this->getJson(route('products.data'));

        $response->assertStatus(200);
        $response->assertJsonCount(5, ['data']);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'product_type_name',
                ],
            ],
        ]);
    }

    /**
     * Test can fetch product types for datatable
     */
    public function test_can_fetch_product_types_for_datatable()
    {
        $productType = ProductType::factory(3)->create();

        $response = $this->getJson(route('products.types.data'));

        $response->assertStatus(200);
        $response->assertJsonCount(3, ['data']);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'units_per_hour',
                ],
            ],
        ]);
    }
}
