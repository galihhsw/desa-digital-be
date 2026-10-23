<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_citizen_can_register_via_api(): void
    {
        $payload = [
            'name' => 'Siti Aminah',
            'email' => 'siti@test.id',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
            'message',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'siti@test.id']);
        $user = User::where('email', 'siti@test.id')->first();
        $this->assertTrue($user->hasRole('head-of-family'));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@test.id',
            'password' => 'secret123',
        ]);
        $user->assignRole('head-of-family');

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@test.id',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json());
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@test.id',
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'testuser@test.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(500);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $user = User::create([
            'name' => 'Citizen User',
            'email' => 'citizen@test.id',
            'password' => 'secret123',
        ]);
        $user->assignRole('head-of-family');

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/me');

        $response->assertStatus(200);
        $this->assertEquals('citizen@test.id', $response->json('data.email'));
        $this->assertEquals('head-of-family', $response->json('data.role'));
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout@test.id',
            'password' => 'secret123',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $response->assertStatus(200);
        $this->assertCount(0, $user->tokens()->get());
    }
}
