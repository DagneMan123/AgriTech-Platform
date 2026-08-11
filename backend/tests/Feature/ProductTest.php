<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
        $this->user = User::factory()->create(['role' => 'farmer']);
        $this->token = $this->user->createToken('auth-token')->plainTextToken;
    }

    public function test_can_get_all_products()
    {
        Product::factory(10)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [['id', 'name', 'price', 'quantity']]])
                 ->assertJsonCount(10, 'data');
    }

    public function test_can_get_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $product->id, 'name' => $product->name]);
    }

    public function test_farmer_can_create_product()
    {
        $productData = [
            'name' => 'Fresh Tomatoes',
            'description' => 'Organically grown tomatoes',
            'price' => 50.00,
            'quantity' => 100,
            'unit' => 'kg',
            'category_id' => $this->category->id,
            'quality_grade' => 'A',
            'organic_certified' => true,
        ];

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
                         ->postJson('/api/products', $productData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Fresh Tomatoes']);

        $this->assertDatabaseHas('products', ['name' => 'Fresh Tomatoes']);
    }

    public function test_farmer_can_update_product()
    {
        $product = Product::factory()->create(['farmer_id' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
                         ->putJson("/api/products/{$product->id}", [
                             'name' => 'Updated Product Name',
                             'price' => 75.00,
                         ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated Product Name']);
    }

    public function test_farmer_can_delete_product()
    {
        $product = Product::factory()->create(['farmer_id' => $this->user->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
                         ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_unauthorized_user_cannot_update_product()
    {
        $otherUser = User::factory()->create(['role' => 'farmer']);
        $product = Product::factory()->create(['farmer_id' => $otherUser->id]);

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
                         ->putJson("/api/products/{$product->id}", [
                             'name' => 'Updated Name',
                         ]);

        $response->assertStatus(403);
    }

    public function test_can_get_products_by_category()
    {
        Product::factory(5)->create(['category_id' => $this->category->id]);
        Product::factory(3)->create();

        $response = $this->getJson("/api/products?category_id={$this->category->id}");

        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data');
    }

    public function test_product_listing_pagination()
    {
        Product::factory(15)->create();

        $response = $this->getJson('/api/products?per_page=10');

        $response->assertStatus(200)
                 ->assertJsonCount(10, 'data')
                 ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_product_creation_requires_authentication()
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Product',
            'price' => 50,
        ]);

        $response->assertStatus(401);
    }

    public function test_product_creation_validation()
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
                         ->postJson('/api/products', [
                             'name' => '',
                             'price' => -10,
                         ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'price']);
    }
}
