<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
                 ->assertJsonStructure([
                     'message',
                     'token',
                     'user' => ['id', 'name', 'email'],
                 ])
                 ->assertJsonPath('message', 'Login successful')
                 ->assertJsonPath('user.email', 'test@example.com');

        $this->assertNotNull($response->json('token'));
    }

    public function test_user_cannot_login_with_invalid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_password()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertUnprocessable()
                 ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*'], 'sanctum');

        $response = $this->postJson('/api/logout');

        $response->assertOk()
                 ->assertJsonPath('message', 'Logout successful');
    }

    public function test_logout_requires_authentication()
    {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
    }

    public function test_can_use_token_for_authenticated_requests()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Login to get token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('token');

        // Use token for authenticated request
        $response = $this->withHeader('Authorization', "Bearer $token")
                        ->getJson('/api/snippets');

        $response->assertOk();
    }
}
