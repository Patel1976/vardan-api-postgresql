<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use App\Models\StaffTimelog;
use App\Models\StaffEmergencyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StaffUserController
{
  public function createStaffUser(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'email|unique:staff_users|max:255',
      'phone' => 'required|string|max:20|unique:staff_users',
      'mpin' => 'string|max:6',
      'address' => 'required|string|max:500',
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
      $staffUser->email = $request->input('email', $staffUser->email);
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
        $staffUser->makeHidden('mpin');
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
    StaffTimelog::create([
      'user_id' => $request->id,
      'logs' => now(),
      'type' => $request->type,
    ]);
    return response()->json([
      'message' => 'Timelog recorded successfully!',
    ], 201);
  }

  public function getStaffTimelog(Request $request)
  {
    try {
      $findStaff = StaffUser::find($request->id);
      if (!$findStaff) {
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

  public function getStaffTimelogByDate(Request $request)
  {
    try {
      $findStaff = StaffUser::find($request->id);
      if (!$findStaff) {
        return response()->json([
          'success' => 0,
          'error' => 1,
          'message' => 'Staff not found',
          'data' => null
        ], 404);
      }
      $Staffalllogs = StaffTimelog::where('user_id', $request->id)->whereDate('logs', $request->date)->get();
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

  public function getStaffTimelogByRange(Request $request)
  {
    try {
      $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
      ]);

      $staff = StaffUser::find($request->id);
      if (!$staff) {
        return response()->json([
          'success' => false,
          'error' => true,
          'message' => 'Staff not found',
          'data' => null
        ], 404);
      }

      $startDate = Carbon::parse($request->start_date)->startOfDay();
      $endDate = Carbon::parse($request->end_date)->endOfDay();
      $timelogs = StaffTimelog::where('user_id', $staff->id)
        ->whereBetween('logs', [$startDate, $endDate])
        ->orderBy('logs', 'asc')
        ->get();

      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Staff timelogs retrieved successfully',
        'data' => $timelogs
      ], 200);

    } catch (\Throwable $th) {
      return response()->json([
        'success' => 0,
        'error' => 1,
        'message' => 'Something went wrong',
        'data' => null
      ], 500);
    }                      // also need to filter with the type of login 
  }
  public function imagelog(Request $request)
  {
      try {
          $request->validate([
              'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
          ]);
          // error_log($request->hasFile('image') ? 'File is present' : 'File is not present');
          $staff = StaffUser::find($request->id);
          if (!$staff) {
              return response()->json([
                  'success' => false,
                  'error' => true,
                  'message' => 'Staff not found',
                  'data' => null
              ], 404);
          }
          if ($request->hasFile('image')) {
              $image = $request->file('image');
              $imageData = $this->convertImageToBytea($image);
              $staffImageLog = StaffEmergencyLog::create([
                  'user_id' => $request->id,
                  'image_logs' => $imageData,
              ]);
              return response()->json([
                  'success' => true,
                  'message' => 'Image uploaded and logged successfully as bytea',
                  'data' => $staffImageLog
              ], 201);
          } else {
              return response()->json([
                  'success' => false,
                  'message' => 'Image upload failed',
                  'data' => null
              ], 400);
          }
      } catch (\Throwable $th) {
          error_log($th->getMessage());
          return response()->json([
              'success' => 0,
              'error' => 1,
              'message' => 'Something went wrong',
              'data' => null
          ], 500);
      }
  }
  private function convertImageToBytea($image)
  {
      $imageData = file_get_contents($image->path()); 
      return base64_encode($imageData);
  }
  
  
  private function convertByteaToImage($base64Data, $outputPath)
  {
      $imageData = base64_decode($base64Data);
      file_put_contents($outputPath, $imageData);
      return $outputPath;
  }
  
    public function getStaffImageLog(Request $request)
    {
        try {
          $staff = StaffUser::find($request->id);
            if (!$staff) {
                return response()->json([
                   'success' => false,
                    'error' => true,
                   'message' => 'Staff not found',
                    'data' => null
                ], 404);
            }
            $staffImageLog = StaffEmergencyLog::where('user_id', $request->id)->pluck('image_logs')->first();
            error_log($request->id);
            error_log($staffImageLog);

            if (!$staffImageLog) {
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'message' => 'Image log not found',
                    'data' => null
                ], 404);
            }
            $imageData = base64_decode($staffImageLog);
            error_log($imageData);
            return response($imageData, 200)
                ->header('Content-Type', 'image/jpeg');

      } catch(\Throwable $th) {
        return response()->json([
         'success' => 0,
          'error' => 1,
         'message' => 'Something went wrong',
          'data' => null
        ], 500);
      }
    } 
}