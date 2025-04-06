<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\{
    Role,
    Permission
};

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $adminUser;
    protected function setUp(): void
    {
        parent::setUp();
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        Permission::firstOrCreate(['name' => 'users.index']);
        Permission::firstOrCreate(['name' => 'users.create']);
        Permission::firstOrCreate(['name' => 'users.update']);
        Permission::firstOrCreate(['name' => 'users.destroy']);
        $adminRole->givePermissionTo([
            'users.index',
            'users.create',
            'users.update',
            'users.destroy',
        ]);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
    }

    public function test_index_success()
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser)->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total',
                ],
            ]);
    }

    public function test_index_validation_error()
    {
        $response = $this->actingAs($this->adminUser)->getJson('/api/v1/users?take=invalid');

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['take'],
            ]);
    }

    public function test_store_success()
    {

        $response = $this->actingAs($this->adminUser)->postJson('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

    }

    public function test_store_validation_error()
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/v1/users', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'role' => '',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_store_role_not_found()
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'nonexistent-role',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['role'],
            ]);
    }

    public function test_update_success()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->adminUser)->putJson('/api/v1/users/' . $user->id, [
            'name' => 'Updated User',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
        ]);
    }

    public function test_update_validation_error()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->adminUser)->putJson('/api/v1/users/' . $user->id, [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'role' => '',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_update_user_not_found()
    {
        $response = $this->actingAs($this->adminUser)->putJson('/api/v1/users/999', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'password' => 'newpassword',
            'role' => 'admin',
        ]);

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    public function test_update_role_not_found()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->adminUser)->putJson('/api/v1/users/' . $user->id, [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'password' => 'newpassword',
            'role' => 'nonexistent-role',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_delete_success()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->adminUser)->deleteJson('/api/v1/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_delete_user_not_found()
    {
        $response = $this->actingAs($this->adminUser)->deleteJson('/api/v1/users/999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }
}
