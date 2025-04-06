<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{
    User,
};
use Illuminate\Support\Facades\{
    Auth,
    Hash
};
use Exception, Validator, DB;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/v1/auth/register",
     * summary="Register a new user",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="name", type="string", example="User pulpoline"),
     * @OA\Property(property="email", type="string", format="email", example="user@pulpoline.com"),
     * @OA\Property(property="password", type="string", example="123789a1A1"),
     * @OA\Property(property="password_confirmation", type="string", example="123789a1A1")
     * )
     * ),
     * @OA\Parameter(
     * name="lang",
     * in="query",
     * description="Language for API responses (en or es)",
     * required=false,
     * @OA\Schema(type="string", enum={"en", "es"})
     * ),
     * @OA\Response(response="200", description="User registered successfully", @OA\JsonContent(
     * @OA\Property(property="data", type="object", @OA\Property(property="access_token", type="string", example="your_access_token"), @OA\Property(property="token_type", type="string", example="Bearer")),
     * @OA\Property(property="message", type="string", example="Successful request, everything went well!"),
     * @OA\Property(property="success", type="boolean", example=true),
     * )),
     * @OA\Response(response="400", description="Validation error", @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Invalid data"),
     * @OA\Property(property="data", type="object", @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email has already been taken.")))
     * ))
     * )
     */
    public function register(Request $request)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:30|min:3',
                'email' => 'required|email|unique:users,email|max:80|min:6',
                'password' => 'required|string|confirmed|min:8|max:20',
            ]);
            if ($validator->fails()) {
                return $this->error(trans('messages.api.invalid_data'), 400, $validator->errors());
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole(config('auth.default_role'));

            $token = $user->createToken('auth_token')->plainTextToken;
            DB::commit();
            return $this->success([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->error(trans('messages.api.error'), 500, $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     * path="/api/v1/auth/login",
     * summary="Login",
     * tags={"Authentication"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="email", type="string", format="email", example="user@pulpoline.com"),
     * @OA\Property(property="password", type="string", example="123789a1A1")
     * )
     * ),
     * @OA\Response(response="200", description="Login successful", @OA\JsonContent(
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="access_token", type="string", example="your_access_token"),
     * @OA\Property(property="user_data", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="User"),
     * @OA\Property(property="email", type="string", example="user@pulpoline.com"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * ),
     * @OA\Property(property="role", type="object",
     * @OA\Property(property="id", type="integer", example=2),
     * @OA\Property(property="name", type="string", example="user"),
     * @OA\Property(property="guard_name", type="string", example="web")
     * ),
     * @OA\Property(property="token_type", type="string", example="Bearer")
     * ),
     * @OA\Property(property="message", type="string", example="Successful request, everything went well!"),
     * @OA\Property(property="success", type="boolean", example=true),
     * )),
     * @OA\Response(response="401", description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->error(trans('messages.api.invalid_data'), 400, $validator->errors());
        }
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error(trans('messages.api.auth.login.error'), 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'access_token' => $token,
            'user_data' => $user->only(['id', 'name', 'email', 'created_at', 'updated_at']),
            'role' => $user->roles->first() ? $user->roles->first()->only(['id', 'name', 'guard_name']) : null,
            'token_type' => 'Bearer',
        ]);
    }
    /**
     * @OA\Post(
     * path="/api/v1/auth/logout",
     * summary="Logout",
     * tags={"Authentication"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="lang",
     * in="query",
     * description="Language for API responses (en or es)",
     * required=false,
     * @OA\Schema(type="string", enum={"en", "es"})
     * ),
     * @OA\Response(response="200", description="Logout successful", @OA\JsonContent(
     * @OA\Property(property="data", type="object", @OA\Property(property="message", type="string", example="Logged out successfully")),
     * @OA\Property(property="message", type="string", example="Successful request, everything went well!"),
     * @OA\Property(property="success", type="boolean", example=true),
     * )),
     * @OA\Response(response="401", description="Unauthorized")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(['message' => trans('messages.api.auth.logout.success')]);
    }
}
