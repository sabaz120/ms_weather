<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Traits\ApiResponseTrait;

/**
 * @OA\Info(
 *             title="Weather & Auth API", 
 *             version="1.0",
 *             description="Documentation for the Weather & Auth API"
 * )
 *
 * @OA\Server(url="http://localhost:8000")
 * @OA\Server(url="http://localhost:8004")
 * @OA\Server(url="http://ms-weather.sytes.net")
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * in="header",
 * name="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT",
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, ApiResponseTrait;
}
