<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminUserController
{

    //Add new user
    public function createUser(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users|max:255',
            'password' => 'required|string|min:8|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Validation failed',
                'data' => [
                    'errors' => $validator->errors()
                ]
            ], 422);
        }
        try {
            $userExists = AdminUser::where('email', $request->email)->first();
            if (!$userExists) {
                $user = AdminUser::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'phone' => $request->phone,
                    'role' => $request->role
                ]);
                if ($request->role) {
                    $user->syncRoles([$request->role]);
                }
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => 'Successfully Registered',
                    'data' => null
                ], 201);
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'User already exists',
                    'data' => null
                ], 409);
            }
        } catch (Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 500);
        }
    }

     //Edit existing user's Data
     public function updateUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'phone' => 'string|max:20',
            'role' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Validation failed',
                'data' => [
                    'errors' => $validator->errors()
                ]
            ], 422);
        }
        try {
            $user = AdminUser::find($id);
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->update();
            $roleName = $request->role;
            if ($roleName) {
                $user->syncRoles([$roleName]); // Sync the new role
            }
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'User updated successfully',
                'data' => null
            ], 200);
        } catch (Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 500);
        }
    } 

    //Fetch all users
    public function getAllUsers()
    {
        try {
            $findAllUsers = AdminUser::with('userRoles')->get();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => '',
                'data' => $findAllUsers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => $e->getMessage(),
                'data' => null
            ], 500);
        }
    }


    //Get user by ID
    public function getUserById($id)
    {
        try {
            $findUser = AdminUser::find($id);
            if (!$findUser) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'User not found',
                    'data' => null
                ], 404);
            }
            $roleId = DB::table('model_has_roles')
                ->where('model_id', $id)
                ->pluck('role_id')
                ->toArray();
            $rolename = DB::table('roles')
                ->where('id', $roleId)
                ->pluck('name')
                ->toArray();

            $roleName = implode(', ', $rolename);

            $userWithRole = $findUser->toArray();
            $userWithRole['role'] = $roleName;

            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => '',
                'data' => $userWithRole,
                // 'role'=> $rolename,
            ], 200);
        } catch (Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 500);
        }
    }

    public function userProfile(Request $request)
    {
        try {
            $bearerToken = $request->header('Authorization');
            $token = substr($bearerToken, 7);
            $userId = JWTAuth::setToken($token)->toUser()->id;
            $findUser = AdminUser::find($userId);
            $userRoleId = DB::table('model_has_roles')->where('model_id', $userId)->pluck('role_id');
            $userModules = DB::table('role_has_permissions')
                ->select('permission_id', 'module')
                ->where('role_id', $userRoleId)
                ->get();
            $response = [];
            foreach ($userModules as $module) {
                $decodedModule = json_decode($module->module, true);
                $response[$module->permission_id] = $decodedModule;
            }
            if ($findUser) {
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => '',
                    'data' => $findUser,
                    'permissions' => $response,
                    'role' => $userRoleId
                ], 200);
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'User not found',
                    'data' => null
                ], 200);
            }
        } catch (Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 500);
        }
    }

    public function editProfile(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'phone' => 'string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Validation failed',
                'data' => [
                    'errors' => $validator->errors()
                ]
            ], 422);
        }
        try {
            $user = AdminUser::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->update();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'User Profile successfully Edited',
                'data' => $user
            ], 200);
        } catch (Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Somethings went wrong',
                'data' => null
            ], 500);
        }
    }

    public function changeUserPassword(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Validation failed',
                'data' => [
                    'errors' => $validator->errors()
                ]
            ], 422);
        }
        try {
            $user = AdminUser::findOrFail($id);
            $user->password = bcrypt($request->password);
            $user->update();
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'Password has successfully updated',
                'data' => $user
            ], 200);
        } catch (Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Somethings went wrong',
                'data' => null
            ], 500);
        }
    }


    //Delete User
    public function deleteUser(Request $request, $id)
    {
        try {
            $bearerToken = $request->header('Authorization');
            $token = substr($bearerToken, 7);
            $userId = JWTAuth::setToken($token)->toUser()->id;
            $findUser = AdminUser::find($id);
            if ($findUser->id === $userId) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'You cannot delete your own account',
                    'data' => null
                ], 403);
            }
            if ($findUser->id === 1) {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'You cannot delete Super Admin account',
                    'data' => null
                ], 403);
            }
            if ($findUser) {
                $findUser->delete();
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => 'Successfully Removed',
                    'data' => null
                ], 200);
            } else {
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'User not found',
                    'data' => null
                ], 401);
            }
        } catch (Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 401);
        }
    }

}
