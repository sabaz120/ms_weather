<?php

namespace Modules\Weather\Http\Controllers\Api\V1;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Weather\Services\WeatherService;
use Exception;
use Modules\Weather\Jobs\SaveSearchHistoryJob;
use Modules\Weather\Entities\SearchHistory;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/v1/weather-module/weather/by-city",
     * summary="Get weather data by city",
     * tags={"Weather"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="city",
     * in="query",
     * description="City name to retrieve weather data",
     * required=true,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="lang",
     * in="query",
     * description="Language for API responses (en or es)",
     * required=false,
     * @OA\Schema(type="string", enum={"en", "es"})
     * ),
     * @OA\Response(
     * response="200",
     * description="Weather data retrieved successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="Weather data retrieved successfully"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="location", type="object",
     * @OA\Property(property="name", type="string", example="London"),
     * @OA\Property(property="region", type="string", example="City of London, Greater London"),
     * @OA\Property(property="country", type="string", example="United Kingdom"),
     * @OA\Property(property="localtime", type="string", example="2024-03-15 15:00")
     * ),
     * @OA\Property(property="current", type="object",
     * @OA\Property(property="temp_c", type="number", example="10"),
     * @OA\Property(property="temp_f", type="number", example="50"),
     * @OA\Property(property="condition", type="object",
     * @OA\Property(property="text", type="string", example="Partly cloudy")
     * ),
     * @OA\Property(property="wind_kph", type="number", example="15"),
     * @OA\Property(property="humidity", type="number", example="60")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response="400",
     * description="Invalid data provided",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Invalid data"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="city", type="array", @OA\Items(type="string", example="Invalid city name"))
     * )
     * )
     * ),
     * @OA\Response(
     * response="500",
     * description="Internal server error",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Error retrieving weather data"),
     * @OA\Property(property="data", type="string", example="Error message from the service")
     * )
     * )
     * )
     */
    public function getByCity(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'city' => 'required|string|max:30|min:3',
            ]);
            if ($validator->fails()) {
                return $this->error(trans('messages.api.invalid_data'), 400, $validator->errors());
            }
            $weatherApiService = new WeatherService();
            $lang = \App::getLocale();
            $city = $request->city;
            $cacheKey = "weather:{$city}:{$lang}";

            $response = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($city, $lang,$weatherApiService) {
                $weatherData = $weatherApiService->getCurrentWeather($city);
                if ($weatherData['success']) {
                    return $weatherData['data'];
                }
                return null;
            });
            if (!$response) {
                throw new Exception(trans('messages.weather_module.weather.error'),500);
            }
            dispatch(new SaveSearchHistoryJob(
                $request->city,
                $response['location']['country'],
                $response['location']['region'],
                auth()->user()->id
            ));
            return $this->success($response, 200, trans('messages.weather_module.weather.success'));
        } catch (Exception $e) {
            return $this->error(trans('messages.weather_module.weather.error'), 500, $e->getMessage());
        }
    }
    /**
     * @OA\Get(
     * path="/api/v1/weather-module/search-history",
     * summary="Get user's search history",
     * tags={"Search History"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="lang",
     * in="query",
     * description="Language for API responses (en or es)",
     * required=false,
     * @OA\Schema(type="string", enum={"en", "es"})
     * ),
     * @OA\Parameter(
     * name="take",
     * in="query",
     * description="Number of items per page",
     * required=false,
     * @OA\Schema(type="integer", minimum=1)
     * ),
     * @OA\Parameter(
     * name="order_direction",
     * in="query",
     * description="Order direction (asc or desc)",
     * required=false,
     * @OA\Schema(type="string", enum={"asc", "desc"})
     * ),
     * @OA\Response(
     * response="200",
     * description="User's search history retrieved successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="string", example="Success"),
     * @OA\Property(property="message", type="string", example="Successful request, everything went well!"),
     * @OA\Property(property="data", type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="user_id", type="integer", example=1),
     * @OA\Property(property="city", type="string", example="London"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     * ),
     * @OA\Property(property="pagination", type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/v1/weather-module/search-history?page=1"),
     * @OA\Property(property="from", type="integer", example=1),
     * @OA\Property(property="last_page", type="integer", example=1),
     * @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/v1/weather-module/search-history?page=1"),
     * @OA\Property(property="links", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="url", type="string", nullable=true, example=null),
     * @OA\Property(property="label", type="string", example="&laquo; Previous"),
     * @OA\Property(property="active", type="boolean", example=false)
     * )
     * ),
     * @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     * @OA\Property(property="path", type="string", example="http://localhost:8000/api/v1/weather-module/search-history"),
     * @OA\Property(property="per_page", type="integer", example=10),
     * @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
     * @OA\Property(property="to", type="integer", example=1),
     * @OA\Property(property="total", type="integer", example=1)
     * )
     * )
     * ),
     * @OA\Response(
     * response="400",
     * description="Invalid data provided",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Invalid data"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="take", type="array", @OA\Items(type="string", example="The take field must be at least 1."))
     * )
     * )
     * ),
     * @OA\Response(
     * response="500",
     * description="Internal server error",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Internal server error"),
     * @OA\Property(property="data", type="string", example="Error message from the service")
     * )
     * )
     * )
     */
    public function getSearchHistory(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'take' => 'nullable|numeric|min:1',
                'order_direction' => 'nullable|in:asc,desc',
            ]);
            if ($validator->fails()) {
                return $this->error(
                    trans('messages.api.invalid_data'),
                    400,
                    $validator->errors()
                );
            }
            $take = request()->get('take', 10);
            $order_direction = request()->get('order_direction', 'desc');
            $rows = SearchHistory::where('user_id', auth()->id())
                ->when($order_direction, function ($query) use ($order_direction) {
                    $query->orderBy('id', $order_direction);
                })
                ->paginate($take);
            return $this->pagination($rows);
        } catch (Exception $e) {
            return $this->error(trans('messages.weather_module.search_history.get.error'), 500, $e->getMessage());
        }
    }
}
