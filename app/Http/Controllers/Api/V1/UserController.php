<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User as Model;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\{
    Role,
};

class UserController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/v1/users",
     * summary="Get list of users",
     * tags={"Users"},
     * security={{"bearerAuth": {}}},
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
     * description="List of users retrieved successfully",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(property="success", type="string", example="Success"),
     * @OA\Property(property="message", type="string", example="Successful request, everything went well!"),
     * @OA\Property(property="data", type="array",
     * @OA\Items(
     * type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", example="john.doe@example.com"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     * ),
     * @OA\Property(property="pagination", type="object",
     * @OA\Property(property="current_page", type="integer", example=1),
     * @OA\Property(property="first_page_url", type="string", example="http://localhost:8000/api/v1/users?page=1"),
     * @OA\Property(property="from", type="integer", example=1),
     * @OA\Property(property="last_page", type="integer", example=1),
     * @OA\Property(property="last_page_url", type="string", example="http://localhost:8000/api/v1/users?page=1"),
     * @OA\Property(property="links", type="array",
     * @OA\Items(type="object",
     * @OA\Property(property="url", type="string", nullable=true, example=null),
     * @OA\Property(property="label", type="string", example="&laquo; Previous"),
     * @OA\Property(property="active", type="boolean", example=false)
     * )
     * ),
     * @OA\Property(property="next_page_url", type="string", nullable=true, example=null),
     * @OA\Property(property="path", type="string", example="http://localhost:8000/api/v1/users"),
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
    public function index(Request $request)
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
            $rows = Model::where('id', "!=", auth()->id())
                ->when($order_direction, function ($query) use ($order_direction) {
                    $query->orderBy('id', $order_direction);
                })
                ->paginate($take);
            return $this->pagination($rows);
        } catch (\Exception $e) {
            return $this->error(trans('messages.api.error'), 500, $e->getMessage());
        }
    } //index()
    /**
     * @OA\Post(
     * path="/api/v1/users",
     * summary="Create a new user and assign a role",
     * tags={"Users"},
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="Sabas Admin"),
     * @OA\Property(property="email", type="string", example="sabas@pulpoline.com"),
     * @OA\Property(property="password", type="string", example="123789a1A1"),
     * @OA\Property(property="role", type="string", example="admin")
     * )
     * ),
     * @OA\Response(
     * response="201",
     * description="User created successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="User created successfully"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="Sabas Admin"),
     * @OA\Property(property="email", type="string", example="sabas@pulpoline.com"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     * )
     * ),
     * @OA\Response(
     * response="400",
     * description="Invalid data provided",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Invalid data"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email must be a valid email address."))
     * )
     * )
     * ),
     * @OA\Response(
     * response="422",
     * description="Role does not exist",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Role does not exist"),
     * @OA\Property(property="data", type="object")
     * )
     * ),
     * @OA\Response(
     * response="500",
     * description="Internal server error",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Internal server error"),
     * @OA\Property(property="data", type="string", example="Error message from the service")
     * )
     * )
     * )
     */
    public function store(Request $request)
    {
        try {
            \DB::beginTransaction();
            $validator = \Validator::make($request->all(), [
                'name' => 'required|string|max:30|min:3',
                'email' => 'required|email|unique:users,email|max:80|min:6',
                'password' => 'required|string|confirmed|min:8|max:20',
                'role' => 'required|string|exists:roles,name',
            ]);

            if ($validator->fails()) {
                return $this->error(trans('messages.api.invalid_data'), 400, $validator->errors());
            }

            $user = Model::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);
            \DB::commit();
            return $this->success($user, 201, trans('messages.user.create.success'));
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->error(trans('messages.user.create.error'), 500, $e->getMessage());
        }
    }
    /**
     * @OA\Put(
     * path="/api/v1/users/{id}",
     * summary="Update a user and their role",
     * tags={"Users"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID of the user to update",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", example="john.doe@example.com"),
     * @OA\Property(property="password", type="string", example="password123"),
     * @OA\Property(property="role", type="string", example="admin")
     * )
     * ),
     * @OA\Response(
     * response="200",
     * description="User updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="User updated successfully"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="John Doe"),
     * @OA\Property(property="email", type="string", example="john.doe@example.com"),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * )
     * )
     * ),
     * @OA\Response(
     * response="400",
     * description="Invalid data provided",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Invalid data"),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email must be a valid email address."))
     * )
     * )
     * ),
     * @OA\Response(
     * response="404",
     * description="User not found",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="User not found"),
     * @OA\Property(property="data", type="object")
     * )
     * ),
     * @OA\Response(
     * response="422",
     * description="Role does not exist",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Role does not exist"),
     * @OA\Property(property="data", type="object")
     * )
     * ),
     * @OA\Response(
     * response="500",
     * description="Internal server error",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Internal server error"),
     * @OA\Property(property="data", type="string", example="Error message from the service")
     * )
     * )
     * )
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'name' => 'nullable|string|max:30|min:3',
                'password' => 'nullable|string|min:8|max:20',
                'role' => 'nullable|string|exists:roles,name',
            ]);

            if ($validator->fails()) {
                return $this->error(trans('messages.api.invalid_data'), 400, $validator->errors());
            }

            $user = Model::find($id);

            if (!$user) {
                return $this->error(trans('messages.user.update.not_found'), 404);
            }

            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            if ($request->has('role')) {
                $user->syncRoles([$request->role]);
            }

            return $this->success($user, 200, trans('messages.user.update.success'));
        } catch (\Exception $e) {
            return $this->error(trans('messages.api.error'), 500, $e->getMessage());
        }
    } //update()

    /**
     * @OA\Delete(
     * path="/api/v1/users/{id}",
     * summary="Delete a user",
     * tags={"Users"},
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID of the user to delete",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response="200",
     * description="User deleted successfully",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=true),
     * @OA\Property(property="message", type="string", example="User deleted successfully"),
     * @OA\Property(property="data", type="object")
     * )
     * ),
     * @OA\Response(
     * response="404",
     * description="User not found",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="User not found"),
     * @OA\Property(property="data", type="object")
     * )
     * ),
     * @OA\Response(
     * response="500",
     * description="Internal server error",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="boolean", example=false),
     * @OA\Property(property="message", type="string", example="Internal server error"),
     * @OA\Property(property="data", type="string", example="Error message from the service")
     * )
     * )
     * )
     */
    public function delete($id)
    {
        try {
            $user = Model::find($id);

            if (!$user) {
                return $this->error(trans('messages.user.update.not_found'), 404);
            }

            $user->delete();

            return $this->success(null, 200, trans('messages.user.deleted'));
        } catch (\Exception $e) {
            return $this->error(trans('messages.api.error'), 500, $e->getMessage());
        }
    } //delete()
}
