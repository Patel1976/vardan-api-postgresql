<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StaffUserController extends Controller
{
  //Create Staff User
  public function createStaffUser(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|email|unique:staff_users|max:255',
      'phone' => 'required|string|max:20|unique:staff_users',
      'mpin' => 'required|string|max:6',
      'address' => 'required|string|max:500',
      'status' => 'required|boolean',
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
      $staffUser = StaffUser::create([
        'uuid' => Str::uuid(),
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'mpin' => $request->mpin,
        'address' => $request->address,
        'status' => $request->status,
        'timestamp' => now(),
      ]);

      return response()->json([
        'success' => 1,
        'error' => 0,
        'message' => 'Staff User successfully created',
        'data' => $staffUser
      ], 201);
    } catch (\Throwable $th) {
      return response()->json([
        'success' => 0,
        'error' => 1,
        'message' => 'Something went wrong',
        'data' => null
      ], 500);
    }
  }

  //Update Staff User
  public function updateStaffUser(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'string|max:255',
      'phone' => 'string|max:20|unique:staff_users,phone,' . $id,
      'mpin' => 'string|max:6',
      'address' => 'string|max:500',
      'status' => 'boolean',
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
      $staffUser = StaffUser::findOrFail($id);
      $staffUser->name = $request->name ?? $staffUser->name;
      $staffUser->phone = $request->phone ?? $staffUser->phone;
      $staffUser->mpin = $request->mpin ?? $staffUser->mpin;
      $staffUser->address = $request->address ?? $staffUser->address;
      $staffUser->status = $request->status ?? $staffUser->status;
      $staffUser->timestamp = now();
      $staffUser->save();

      return response()->json([
        'success' => 1,
        'error' => 0,
        'message' => 'Staff User updated successfully',
        'data' => $staffUser
      ], 200);
    } catch (\Throwable $th) {
      return response()->json([
        'success' => 0,
        'error' => 1,
        'message' => 'Something went wrong',
        'data' => null
      ], 500);
    }
  }

  //Get all the users
  public function getAllStaffUsers()
  {
    try {
      $staffUsers = StaffUser::all();
      return response()->json([
        'success' => 1,
        'error' => 0,
        'message' => '',
        'data' => $staffUsers
      ], 200);
    } catch (\Throwable $th) {
      return response()->json([
        'success' => 0,
        'error' => 1,
        'message' => 'Something went wrong',
        'data' => null
      ], 500);
    }
  }

  //get staff user by id
  public function getStaffUserById($id)
  {
    try {
      $staffUser = StaffUser::find($id);
      if ($staffUser) {
        return response()->json([
          'success' => 1,
          'error' => 0,
          'message' => '',
          'data' => $staffUser
        ], 200);
      } else {
        return response()->json([
          'success' => 0,
          'error' => 1,
          'message' => 'Staff User not found',
          'data' => null
        ], 404);
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



  public function deleteStaffUser($id)
  {
    try {
      $staffUser = StaffUser::find($id);
      if ($staffUser) {
        $staffUser->delete();
        return response()->json([
          'success' => 1,
          'error' => 0,
          'message' => 'Staff User successfully deleted',
          'data' => null
        ], 200);
      } else {
        return response()->json([
          'success' => 0,
          'error' => 1,
          'message' => 'Staff User not found',
          'data' => null
        ], 404);
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
}
