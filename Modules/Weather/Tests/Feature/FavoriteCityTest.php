<?php

namespace Modules\Weather\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Modules\Weather\Entities\FavoriteCity;

class FavoriteCityTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_favorite_city_success()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('api/v1/weather-module/favorite-cities', [
            'city' => 'London',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'city',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('favorite_cities', [
            'user_id' => $user->id,
            'city' => 'London',
        ]);
    }

    public function test_add_favorite_city_validation_error()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('api/v1/weather-module/favorite-cities', [
            'city' => '',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['city'],
            ]);
    }

    public function test_remove_favorite_city_success()
    {
        $user = User::factory()->create();
        $favoriteCity = FavoriteCity::create([
            'user_id' => $user->id,
            "city" => 'London',
        ]);

        $response = $this->actingAs($user)->deleteJson("api/v1/weather-module/favorite-cities/{$favoriteCity->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);

        $this->assertDatabaseMissing('favorite_cities', [
            'id' => $favoriteCity->id,
        ]);
    }

    public function test_remove_favorite_city_not_found()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson('api/v1/weather-module/favorite-cities/999');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    public function test_get_favorite_cities_success()
    {
        $user = User::factory()->create();
        FavoriteCity::create([
            'user_id' => $user->id,
            'city' => 'London',
        ]);
        FavoriteCity::create([
            'user_id' => $user->id,
            'city' => 'New York',
        ]);
        FavoriteCity::create([
            'user_id' => $user->id,
            'city' => 'Tokyo',
        ]);
        $response = $this->actingAs($user)->getJson('api/v1/weather-module/favorite-cities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'city',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'pagination',
            ]);
    }

    public function test_get_favorite_cities_validation_error()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('api/v1/weather-module/favorite-cities?take=invalid');

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['take'],
            ]);
    }
}
