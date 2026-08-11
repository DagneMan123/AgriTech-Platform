<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private $buyer;
    private $farmer;
    private $buyerToken;
    private $farmerToken;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->farmer = User::factory()->create(['role' => 'farmer']);
        $this->buyerToken = $this->buyer->createToken('auth-token')->plainTextToken;
        $this->farmerToken = $this->farmer->createToken('auth-token')->plainTextToken;
        $this->product = Product::factory()->create(['farmer_id' => $this->farmer->id]);
    }

    public function test_buyer_can_create_order()
    {
        $orderData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => $this->product->price,
                ]
            ],
            'delivery_address' => '123 Main St',
            'delivery_region' => 'Addis Ababa',
            'delivery_zone' => 'Zone 1',
            'delivery_woreda' => 'Woreda 1',
        ];

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->buyerToken}"])
                         ->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'order_number', 'status', 'total_amount']);

        $this->assertDatabaseHas('orders', ['buyer_id' => $this->buyer->id]);
    }

    public function test_buyer_can_view_own_orders()
    {
        $order = Order::factory()->create(['buyer_id' => $this->buyer->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->buyerToken}"])
                         ->getJson('/api/my-orders');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $order->id]);
    }

    public function test_farmer_can_view_orders_for_products()
    {
        $order = Order::factory()->create();
        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => $this->product->price,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->farmerToken}"])
                         ->getJson('/api/my-orders');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $order->id]);
    }

    public function test_farmer_can_accept_order()
    {
        $order = Order::factory()->create();
        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => $this->product->price,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->farmerToken}"])
                         ->postJson("/api/orders/{$order->id}/accept");

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'accepted']);
    }

    public function test_farmer_can_reject_order()
    {
        $order = Order::factory()->create();
        $order->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => $this->product->price,
        ]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->farmerToken}"])
                         ->postJson("/api/orders/{$order->id}/reject");

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'rejected']);
    }

    public function test_can_get_order_details()
    {
        $order = Order::factory()->create();

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->buyerToken}"])
                         ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'order_number', 'status', 'items', 'total_amount']);
    }

    public function test_order_creation_validation()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->buyerToken}"])
                         ->postJson('/api/orders', [
                             'items' => [],
                             'delivery_address' => '',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['items', 'delivery_address']);
    }

    public function test_non_authenticated_user_cannot_create_order()
    {
        $response = $this->postJson('/api/orders', [
            'items' => [['product_id' => 1, 'quantity' => 1]],
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_view_all_orders()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = $admin->createToken('auth-token')->plainTextToken;

        Order::factory(5)->create();

        $response = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
                         ->getJson('/api/admin/orders');

        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data');
    }

    public function test_order_status_log_created()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'new_status' => 'pending',
        ]);
    }
}
