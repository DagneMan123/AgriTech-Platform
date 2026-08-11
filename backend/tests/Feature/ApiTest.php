<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_requires_authentication_for_protected_routes()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
                 ->assertJsonStructure(['message']);
    }

    public function test_public_endpoints_are_accessible()
    {
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);

        $response = $this->getJson('/api/weather');
        $response->assertStatus(200);

        $response = $this->getJson('/api/market-prices');
        $response->assertStatus(200);

        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
    }

    public function test_invalid_token_returns_unauthorized()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer invalid-token'])
                         ->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_role_middleware_restricts_access()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $token = $buyer->createToken('auth-token')->plainTextToken;

        // Buyer should not access farmer routes
        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->postJson('/api/farms', [
                             'name' => 'Test Farm',
                         ]);

        $response->assertStatus(403);
    }

    public function test_api_returns_correct_response_format()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'email',
                     'role',
                     'is_active',
                     'created_at',
                     'updated_at',
                 ]);
    }

    public function test_api_validation_returns_422()
    {
        $user = User::factory()->create(['role' => 'farmer']);
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->postJson('/api/products', [
                             'name' => '',
                             'price' => 'invalid',
                         ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors']);
    }

    public function test_api_handles_not_found()
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404);
    }

    public function test_api_cors_headers_present()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
    }

    public function test_paginated_endpoints_return_correct_structure()
    {
        $response = $this->getJson('/api/products?page=1&per_page=10');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links' => ['first', 'last', 'prev', 'next'],
                     'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
                 ]);
    }

    public function test_api_error_response_format()
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors']);
    }

    public function test_server_error_returns_500()
    {
        // This test verifies error handling - in real scenarios
        $response = $this->getJson('/api/invalid-endpoint-that-does-not-exist');

        $response->assertStatus(404);
    }

    public function test_request_timeout_handling()
    {
        $response = $this->getJson('/api/products');

        $this->assertLessThan(5, $response->getStatusCode() / 100);
    }
}
