<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@pulpoline.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@pulpoline.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'access_token' => $response->json('data.access_token'),
                    'user_data' => $response->json('data.user_data'),
                    'role' => $response->json('data.role'),
                    'token_type' => 'Bearer',
                ],
                'message' => trans('messages.api.success'),
                'success' => true,
            ]);
    }

    public function test_login_invalid_credentials()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@pulpoline.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_validation_fails()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => trans('messages.api.invalid_data'),
                'data' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ],
            ]);
    }

    public function test_user_can_register()
    {
        \Spatie\Permission\Models\Role::create(['name' => config('auth.default_role')]);
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@pulpoline.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'access_token' => $response->json('data.access_token'),
                    'token_type' => 'Bearer',
                ],
                'message' => trans('messages.api.success'),
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@pulpoline.com',
        ]);
    }

    public function test_register_validation_fails()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => trans('messages.api.invalid_data'),
                'data' => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email field must be a valid email address.'],
                    'password' => [
                        'The password field confirmation does not match.',
                        "The password field must be at least 8 characters."
                    ],
                ],
            ]);
    }

    public function test_register_unique_email()
    {
        \Spatie\Permission\Models\Role::create(['name' => config('auth.default_role')]);
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@pulpoline.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Another User',
            'email' => 'test@pulpoline.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => trans('messages.api.invalid_data'),
                'data' => [
                    'email' => ['The email has already been taken.'],
                ],
            ]);
    }

    public function test_user_can_logout()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@pulpoline.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'message' => trans('messages.api.auth.logout.success'),
                ],
                'message' => trans('messages.api.success'),
                'success' => true,
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_logout_unauthenticated()
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(403);
    }

}
