<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

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
            'role' => 'required|string|max:20'

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
                $uid = AdminUser::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                    'phone' => $request->phone,
                ]);
                $role = Role::findByName($request->role);
                $uid->assignRole($role);
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
        } catch (\Throwable $th) {
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
         try {
             $user = AdminUser::findOrFail($id); // Check if the user exists
     
             // Update user details
             $user->name = $request->input('name', $user->name);
             $user->email = $request->input('email', $user->email);
     
             // Update password if provided and matches confirm_password
             if ($request->has('password') && $request->input('password') === $request->input('confirm_password')) {
                 $user->password = Hash::make($request->input('password'));
             }
     
             $user->save();
     
             return response()->json([
                 'success' => 1,
                 'error' => 0,
                 'message' => 'User updated successfully',
                 'data' => $user
             ], 200);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => 0,
                 'error' => 1,
                 'message' => 'Something went wrong: ' . $e->getMessage(),
                 'data' => null
             ], 500);
         }
     }
     

    //Fetch all users
    public function getAllUsers()
    { 
        try {
            $findAllUsers = AdminUser::all();            
            return response()->json([
                'success' => 1,
                'error' => 0,
                'message' => 'All Users :-',
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
        $findUser = AdminUser::findOrFail($id);
        return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => 'User found',
            'data' => $findUser
        ], 200);
    } catch (ModelNotFoundException $e) {
        return response()->json([
            'success' => 0,
            'error' => 1,
            'message' => 'User not found',
            'data' => null
        ], 404);
    } catch (Throwable $th) {
        return response()->json([
            'success' => 0,
            'error' => 1,
            'message' => 'Something went wrong',
            'data' => null
        ], 500);
    }
}

    //Delete User
    public function deleteUser(Request $request , $id)
    {
        try {
            $bearerToken =  $request->header('Authorization');
            $token = substr($bearerToken, 7);
            $userId = JWTAuth::setToken($token)->toUser()->id;
            $findUser = AdminUser::find($id);
            if($findUser === null){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'User not found',
                    'data' => null
                ], 401);
            }
            if($findUser->id === $userId){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'You cannot delete your own account',
                    'data' => null
                ], 403);
            }
            if($findUser->id === 1){
                return response()->json([
                    'success' => 0,
                    'error' => 1,
                    'message' => 'You cannot delete Super Admin account',
                    'data' => null
                ], 403);
            }
                $findUser->delete();
                return response()->json([
                    'success' => 1,
                    'error' => 0,
                    'message' => 'Successfully Removed',
                    'data' => null
                ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Something went wrong',
                'data' => null
            ], 401);
        }
    }

}
