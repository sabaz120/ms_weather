<?php

namespace Modules\Weather\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\Weather\Jobs\SaveSearchHistoryJob;
use Modules\Weather\Services\WeatherService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Modules\Weather\Entities\SearchHistory;
use Illuminate\Support\Facades\Cache;

class WeatherControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_weather_by_city_success()
    {
        Queue::fake();

        $user = User::factory()->create();

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'location' => [
                    'name' => 'London',
                    'region' => 'City of London, Greater London',
                    'country' => 'United Kingdom',
                    'localtime' => '2024-03-15 15:00'
                ],
                'current' => [
                    'temp_c' => 10,
                    'temp_f' => 50,
                    'condition' => ['text' => 'Partly cloudy'],
                    'wind_kph' => 15,
                    'humidity' => 60,
                ],
            ]);

        $response = $this->actingAs($user)->getJson('api/v1/weather-module/weather/by-city?city=London');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'location',
                    'current',
                ],
            ]);

        Queue::assertPushed(SaveSearchHistoryJob::class);
    }

    public function test_get_weather_by_city_api_error()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('api/v1/weather-module/weather/by-city?city=Londons');

        $response->assertStatus(500)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_get_search_history_success()
    {
        $user = User::factory()->create();
        SearchHistory::create([
            'user_id' => $user->id,
            'city' => 'London',
            'country' => 'United Kingdom',
            'region' => 'City of London, Greater London',
        ]);

        $response = $this->actingAs($user)->getJson('api/v1/weather-module/search-history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
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

    public function test_get_search_history_validation_error()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('api/v1/weather-module/search-history?take=invalid');

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }
}
