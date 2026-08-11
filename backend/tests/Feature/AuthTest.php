<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Farmer',
            'email' => 'farmer@test.com',
            'phone' => '251911111111',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'farmer',
            'region' => 'Oromia',
            'zone' => 'West Hararghe',
            'woreda' => 'Chiro',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'user', 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'farmer@test.com',
            'role' => 'farmer',
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'buyer',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user', 'token']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    public function test_login_fails_for_suspended_user()
    {
        User::factory()->create([
            'email' => 'suspended@example.com',
            'password' => bcrypt('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'suspended@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_view_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword123'),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->postJson('/api/password/change', [
                             'current_password' => 'oldpassword123',
                             'password' => 'newpassword123',
                             'password_confirmation' => 'newpassword123',
                         ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password changed successfully']);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_forgot_password_sends_reset_link()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200);
    }

    public function test_registration_validation()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'email', 'password', 'phone', 'role']);
    }
}
