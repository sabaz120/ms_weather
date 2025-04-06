<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\V1\{
    AuthController,
    UserController,
};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return 'Hello World';
});
Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->middleware('can:users.index');
            Route::post('/', [UserController::class, 'store'])->middleware('can:users.create');
            Route::put('/{id}', [UserController::class, 'update'])->middleware('can:users.update');
            Route::delete('/{id}', [UserController::class, 'delete'])->middleware('can:users.destroy');
        });
    });
});
