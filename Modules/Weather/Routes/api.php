<?php

use Illuminate\Support\Facades\Route;
use Modules\Weather\Http\Controllers\Api\V1\{
    FavoriteCityController,
    WeatherController
};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//V1
Route::middleware('auth:sanctum')->prefix('v1/weather-module')->group(function () {
    //weather
    Route::prefix('weather')->middleware('auth:sanctum')->group(function () {
        Route::get('by-city', [
            'uses' => WeatherController::class . '@getByCity',
            'as' => 'weather.get_by_city',
        ]);
    });//weather

    //favorite-cities
    Route::prefix('favorite-cities')->group(function () {
        Route::post('/', [
            'uses' => FavoriteCityController::class . '@addFavorite',
            'as' => 'favorite-cities.add',
        ]);
        Route::delete('/{id}', [
            'uses' => FavoriteCityController::class . '@removeFavorite',
            'as' => 'favorite-cities.remove',
        ]);
        Route::get('/', [
            'uses' => FavoriteCityController::class . '@getFavorites',
            'as' => 'favorite-cities.get',
        ]);
    });//favorite-cities
    
    //search-history
    Route::prefix('search-history')->group(function () {
        Route::get('/', [
            'uses' => WeatherController::class . '@getSearchHistory',
            'as' => 'search-history.get',
        ]);
    });
});