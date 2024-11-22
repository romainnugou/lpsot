<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Order;
use App\Models\OrderItem;

class OrderFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test can see orders page
     */
    public function test_can_see_orders_page()
    {
        $response = $this->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee('🗂️ Orders');
    }

    /**
     * Test can see order details page
     */
    public function test_can_see_order_details_page()
    {
        $order = Order::factory()->create();

        $response = $this->get(route('orders.show', $order->id));

        $response->assertStatus(200);
        $response->assertSee('ℹ️ Order details');
    }

    /**
     * Test cannot see order details page for non existent order
     */
    public function test_cannot_see_order_details_page_for_non_existent_order()
    {
        $response = $this->get(route('orders.show', -1));

        $response->assertStatus(404);
    }

    /**
     * Test can see create order page
     */
    public function test_can_see_create_order_page()
    {
        $response = $this->get(route('orders.create'));

        $response->assertStatus(200);
        $response->assertSee('✨ New order');
    }

    /**
     * Test can create order with valid data one order item
     */
    public function test_can_create_order_with_valid_data_one_order_item()
    {
        $productType = ProductType::factory()->create();
        $product = Product::factory()->create([
            'product_type_id' => $productType->id
        ]);

        $data = [
            'customer_name' => 'George Harrison',
            'need_by' => now()->addDays(30)->toDateString(),
            'order_items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1000
                ]
            ],
        ];

        $response = $this->post(route('orders.store'), $data);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'George Harrison'
        ]);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 1000
        ]);
    }

    /**
     * Test can create order with valid data several order items
     */
    public function test_can_create_order_with_valid_data_several_order_items()
    {
        $productType = ProductType::factory()->create();
        $products = Product::factory(3)->create([
            'product_type_id' => $productType->id
        ]);

        $data = [
            'customer_name' => 'George Harrison',
            'need_by' => now()->addDays(30)->toDateString(),
            'order_items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 1000
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 10000
                ],
                [
                    'product_id' => $products[2]->id,
                    'quantity' => 20000
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $data);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'George Harrison'
        ]);
        $this->assertDatabaseCount('order_items', 3);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $products[0]->id,
            'quantity' => 1000
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $products[1]->id,
            'quantity' => 10000
        ]);
        $this->assertDatabaseHas('order_items', [
            'product_id' => $products[2]->id,
            'quantity' => 20000
        ]);
    }

    /**
     * Test cannot create order with need_by date in the past
     */
    public function test_cannot_create_order_with_need_by_date_in_the_past()
    {
        $productType = ProductType::factory()->create();
        $products = Product::factory(1)->create([
            'product_type_id' => $productType->id
        ]);

        $data = [
            'customer_name' => 'Paul McCartney',
            'need_by' => now()->subDay()->toDateString(),
            'order_items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 1000
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $data);

        $response->assertSessionHasErrors(['need_by']);
        $this->assertDatabaseMissing('orders', [
            'customer_name' => 'Paul McCartney'
        ]);
    }

    /**
     * Test cannot create order with no customer name
     */
    public function test_cannot_create_order_with_no_customer_name()
    {
        $productType = ProductType::factory()->create();
        $products = Product::factory(1)->create([
            'product_type_id' => $productType->id
        ]);

        $data = [
            'customer_name' => '',
            'need_by' => now()->addDays(30)->toDateString(),
            'order_items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 1000
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $data);

        $response->assertSessionHasErrors(['customer_name']);
        $this->assertDatabaseMissing('orders', [
            'customer_name' => ''
        ]);
    }

    /**
     * Test cannot create order with order item with negative quantity
     */
    public function test_cannot_create_order_with_order_item_with_negative_quantity()
    {
        $productType = ProductType::factory()->create();
        $products = Product::factory(1)->create([
            'product_type_id' => $productType->id
        ]);

        $data = [
            'customer_name' => 'Robert Plant',
            'need_by' => now()->addDays(30)->toDateString(),
            'order_items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => -100
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $data);

        $response->assertSessionHasErrors(['order_items.*.quantity']);
        $this->assertDatabaseMissing('orders', [
            'customer_name' => 'Robert Plant'
        ]);
    }

    /**
     * Test cannot create order without order item
     */
    public function test_cannot_create_order_without_order_item()
    {
        $productType = ProductType::factory()->create();
        $products = Product::factory(1)->create([
            'product_type_id' => $productType->id
        ]);

        $data = [
            'customer_name' => 'Ringo Starr',
            'need_by' => now()->addDays(30)->toDateString(),
            'order_items' => [],
        ];

        $response = $this->post(route('orders.store'), $data);

        $response->assertSessionHasErrors(['order_items']);
        $this->assertDatabaseMissing('orders', [
            'customer_name' => 'Ringo Starr'
        ]);
    }

    /**
     * Test cannot create order with products with different types
     */
    public function test_cannot_create_order_with_products_with_different_types()
    {
        $productType1 = ProductType::factory()->create();
        $productType2 = ProductType::factory()->create();
        $product1 = Product::factory()->create([
            'product_type_id' => $productType1->id
        ]);
        $product2 = Product::factory()->create([
            'product_type_id' => $productType2->id
        ]);

        $data = [
            'customer_name' => 'Jimmy Page',
            'need_by' => now()->addDays(30)->toDateString(),
            'order_items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 1000
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 10000
                ],
            ],
        ];

        $response = $this->post(route('orders.store'), $data);

        $response->assertSessionHasErrors(['order_items']);
        $this->assertDatabaseMissing('orders', [
            'customer_name' => 'Jimmy Page'
        ]);
    }

    /**
     * Test can delete order
     */
    public function test_can_delete_order()
    {
        $order = Order::factory()->create();
        OrderItem::factory(2)->create(['order_id' => $order->id]);

        $response = $this->delete(route('orders.delete', $order->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseCount('order_items', 0);
    }

    /**
     * Test cannont delete non eixstant order
     */
    public function test_cannot_delete_non_existent_order()
    {
        $response = $this->delete(route('orders.delete', -1));

        $response->assertStatus(404);
    }

    /**
     * Test can fetch orders for datatable
     */
    public function test_can_fetch_orders_for_datatable()
    {
        $orders = Order::factory(10)->create();
        foreach ($orders as $order) {
            OrderItem::factory()->create(['order_id' => $order->id]);
        }

        $response = $this->getJson(route('orders.data'));

        $response->assertStatus(200);
        $response->assertJsonCount(10, ['data']);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'created_at',
                    'customer_name',
                    'product_type_name',
                    'need_by',
                    'actions',
                ],
            ],
        ]);
    }

    /**
     * Test can fetch order items for datatable
     */
    public function test_can_fetch_order_items_for_datatable()
    {
        $order = Order::factory()->create();
        $productType = ProductType::factory()->create();
        $products = Product::factory(3)->create(['product_type_id' => $productType->id]);
        foreach ($products as $product) {
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id
            ]);
        }

        $response = $this->getJson(route('orders.items.data', ['id' => $order->id]));

        $response->assertStatus(200);
        $response->assertJsonCount(3, ['data']);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'product_id',
                    'quantity',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }
}
