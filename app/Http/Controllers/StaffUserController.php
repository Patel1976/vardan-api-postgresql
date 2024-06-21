<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use App\Models\StaffTimelog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StaffUserController
{
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

  public function updateStaffUser(Request $request, $id)
  {
    $staffUser = StaffUser::find($id);
    if (!$staffUser) {
      return response()->json([
        'success' => false,
        'error' => true,
        'message' => 'Staff User not found',
        'data' => null
      ], 404);
    }
    $validator = Validator::make($request->all(), [
      'name' => 'string|max:255',
      'phone' => 'string|max:20|unique:staff_users,phone,' . $id,
      'mpin' => 'string|max:6',
      'address' => 'string|max:500',
      'status' => 'boolean',
    ]);
    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'error' => true,
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }
    try {

      $staffUser->name = $request->input('name', $staffUser->name);
      $staffUser->phone = $request->input('phone', $staffUser->phone);
      $staffUser->mpin = $request->input('mpin', $staffUser->mpin);
      $staffUser->address = $request->input('address', $staffUser->address);
      $staffUser->status = $request->input('status', $staffUser->status);
      $staffUser->save();

      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Staff User updated successfully',
        'data' => $staffUser
      ], 200);

    } catch (\Throwable $th) {
      return response()->json([
        'success' => false,
        'error' => true,
        'message' => 'Something went wrong',
        'data' => null
      ], 500);
    }
  }


  public function getAllStaffUsers()
  {
    try {
      $staffUsers = StaffUser::all();
      return response()->json([
        'success' => 1,
        'error' => 0,
        'message' => ' Staff Users List',
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

  public function staffTimelog(Request $request)
  {
    $request->validate([
      'type' => 'required|in:in,out'
    ]);
    error_log($request->id);
    StaffTimelog::create([
      'user_id' => $request->id,
      'logs' => now(),
      'type' => $request->type,
    ]);
    error_log($request->type);
    return response()->json([
      'message' => 'Timelog recorded successfully!',
    ], 201);
  }

  public function getStaffTimelog(Request $request)
  {
    try {
      $findStaff = StaffUser::find($request->id);
      if(!$findStaff){
        return response()->json([
         'success' => 0,
          'error' => 1,
         'message' => 'Staff not found',
          'data' => null
        ], 404);
      }
      $Staffalllogs = StaffTimelog::where('user_id', $request->id)->get();
      return response()->json([
       'success' => 1,
        'error' => 0,
       'message' => 'Staff Timelogs',
        'data' => $Staffalllogs
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
}