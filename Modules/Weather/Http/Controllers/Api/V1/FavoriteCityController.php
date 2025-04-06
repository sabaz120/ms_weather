<?php

namespace Modules\Weather\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Weather\Entities\FavoriteCity;
use Exception, Validator, DB;

class FavoriteCityController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/v1/weather-module/favorite-cities",
     * summary="Add a city to favorites",
     * tags={"Favorite Cities"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="lang",
     * in="query",
     * description="Language for API responses (en or es)",
     * required=false,
     * @OA\Schema(type="string", enum={"en", "es"})
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="city", type="string", example="London")
     * )
     * ),
     * @OA\Response(
     * response="201",
     * description="City added to favorites successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="City added to favorites"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="user_id", type="integer", example=1),
     * @OA\Property(property="city", type="string", example="London"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time")
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
     * @OA\Property(property="city", type="array", @OA\Items(type="string", example="The city field is required."))
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
    public function addFavorite(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'city' => 'required|string|max:30|min:3',
            ]);
            if ($validator->fails()) {
                return $this->error(
                    trans('messages.api.invalid_data'),
                    400,
                    $validator->errors()
                );
            }
            $verifyIsCreated = FavoriteCity::where('user_id', auth()->id())
                ->where('city', $request->city)
                ->exists();
            if ($verifyIsCreated) {
                return $this->error(trans('messages.weather_module.favorite_cities.add.city_exists'), 400);
            }
            $modelCreated = FavoriteCity::create([
                'user_id' => auth()->id(),
                'city' => $request->city,
            ]);
            DB::commit();
            return $this->success($modelCreated, 201, trans('messages.weather_module.favorite_cities.add.success'));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error(trans('messages.weather_module.favorite_cities.add.error'), 500, $e->getMessage());
        }
    }
    /**
     * @OA\Delete(
     * path="/api/v1/weather-module/favorite-cities/{id}",
     * summary="Remove a city from favorites",
     * tags={"Favorite Cities"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="lang",
     * in="query",
     * description="Language for API responses (en or es)",
     * required=false,
     * @OA\Schema(type="string", enum={"en", "es"})
     * ),
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID of the favorite city to remove",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response="200",
     * description="City removed from favorites successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="City removed from favorites")
     * )
     * ),
     * @OA\Response(
     * response="404",
     * description="Favorite city not found",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Favorite city not found")
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
    public function removeFavorite($id)
    {
        try {
            DB::beginTransaction();
            $model = FavoriteCity::where('user_id', auth()->id())
                ->whereId($id)
                ->first();
            if (!$model) {
                return $this->error(trans('messages.weather_module.favorite_cities.remove.not_found'), 404);
            }
            $model->delete();
            DB::commit();
            return $this->success([], 200, trans('messages.weather_module.favorite_cities.remove.success'));
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error(trans('messages.weather_module.favorite_cities.remove.error'), 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     * path="/api/v1/weather-module/favorite-cities",
     * summary="Get list of favorite cities",
     * tags={"Favorite Cities"},
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
     * description="List of favorite cities retrieved successfully",
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
     * @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/v1/weather-module/favorite-cities?page=1"),
     * @OA\Property(property="from", type="integer", example=1),
     * @OA\Property(property="last_page", type="integer", example=1),
     * @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/v1/weather-module/favorite-cities?page=1"),
     * @OA\Property(property="links", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="url", type="string", nullable=true, example=null),
     * @OA\Property(property="label", type="string", example="&laquo; Previous"),
     * @OA\Property(property="active", type="boolean", example=false)
     * )
     * ),
     * @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     * @OA\Property(property="path", type="string", example="http://localhost:8000/api/v1/weather-module/favorite-cities"),
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
    public function getFavorites(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
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
            $favorites = FavoriteCity::where('user_id', auth()->id())
                ->when($order_direction, function ($query) use ($order_direction) {
                    $query->orderBy('id', $order_direction);
                })
                ->paginate($take);
            return $this->pagination($favorites);
        } catch (Exception $e) {
            return $this->error(trans('messages.weather_module.favorite_cities.get.error'), 500, $e->getMessage());
        }
    }
}
