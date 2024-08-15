<?php

namespace App\Http\Controllers;

use App\Models\StaffUser;
use App\Models\StaffTimelog;
use App\Models\Staff_emergency_logs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


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

  public function searchStaff(Request $request)
  {
    $query = $request->input('query');

    $staffQuery = StaffUser::select('id', 'name', 'uuid');

    if (!empty($query)) {
      $staffQuery->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($query) . '%']);
    }

    $staff = $staffQuery->limit(5)->get();

    return response()->json($staff);
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

      $staffTimeLogQuery = StaffTimelog::where('user_id', $request->id);

      // Handling date filters
      if (isset($request->start_date) && !empty($request->start_date)) {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $staffTimeLogQuery->where('logs', '>=', $startDate);
      }

      if (isset($request->end_date) && !empty($request->end_date)) {
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $staffTimeLogQuery->where('logs', '<=', $endDate);
      }

      // Handling sorting
      $sort = $request->get('sort', 'id');
      $direction = $request->get('direction', 'DESC');
      $staffTimeLogQuery->orderBy($sort, $direction);

      // Handling pagination
      $recordPerPage = $request->get('recordPerPage', env('RECORDS_PER_PAGE', 10));
      $pageNumber = $request->get('pageNumber');

      // Join with StaffUser to get the staff user name
      $staffTimeLogQuery = $staffTimeLogQuery
        ->join('staff_users', 'staff_timelogs.user_id', '=', 'staff_users.id')
        ->select('staff_timelogs.*', 'staff_users.name as staff_name');

      if (isset($pageNumber) && !empty($pageNumber)) {
        $staffTimeLogQuery = $staffTimeLogQuery->paginate($recordPerPage, ['*'], 'page', $pageNumber);
      } else {
        $staffTimeLogQuery = $staffTimeLogQuery->get();
      }

      $staffData = isset($pageNumber) && !empty($pageNumber) ? $staffTimeLogQuery->toArray() : ['data' => $staffTimeLogQuery];

      if (empty($staffData['data'])) {
        return response()->json([
          'success' => 0,
          'error' => 1,
          'message' => 'Staff data is null',
          'data' => null
        ], 300);
      }

      return response()->json([
        'success' => 1,
        'error' => 0,
        'message' => 'Staff Timelogs',
        'data' => $staffData
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
  public function getAllStaffTimelog(Request $request)
  {
    try {
      $staffTimeLogQuery = StaffTimelog::query();

      if (isset($request->start_date) && !empty($request->start_date)) {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $staffTimeLogQuery->where('logs', '>=', $startDate);
      }

      if (isset($request->end_date) && !empty($request->end_date)) {
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $staffTimeLogQuery->where('logs', '<=', $endDate);
      }

      $sort = $request->get('sort', 'id');
      $direction = $request->get('direction', 'DESC');
      $staffTimeLogQuery->orderBy($sort, $direction);

      $staffTimeLogQuery = $staffTimeLogQuery
        ->join('staff_users', 'staff_timelogs.user_id', '=', 'staff_users.id')
        ->select('staff_timelogs.*', 'staff_users.name as staff_name');

      if ($request->response === "Download") {
        $staffData = $staffTimeLogQuery->get()->toArray();
        return response()->json([
          'success' => 1,
          'error' => 0,
          'message' => 'Staff Timelogs',
          'data' => $staffData
        ], 200);
      }

      $recordPerPage = $request->get('recordPerPage', env('RECORDS_PER_PAGE', 10));
      $pageNumber = $request->get('pageNumber', 1);

      $staffTimeLogQuery = $staffTimeLogQuery->paginate($recordPerPage, ['*'], 'page', $pageNumber);
      $staffData = $staffTimeLogQuery->toArray();

      return response()->json([
        'success' => 1,
        'error' => 0,
        'message' => 'Staff Timelogs',
        'data' => $staffData
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => 0,
        'error' => 1,
        'message' => 'Something went wrong',
        'data' => null
      ], 500);
    }
  }

  public function CreateImagelog(Request $request)
  {
    // Validate the request
    $validator = Validator::make($request->all(), [
      'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
      'user_id' => 'required|exists:staff_users,id',
      'description' => 'nullable|string',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => $validator->errors()], 422);
    }

    if ($request->file('image')) {
      $image = $request->file('image');
      $path = $image->store('images/' . $request->user_id, 'public');
      $imageUrl = url('storage/' . $path);

      $log = Staff_emergency_logs::create([
        'user_id' => $request->user_id,
        'image_path' => $imageUrl,
        'description' => $request->description,
      ]);

      return response()->json([
        'message' => 'Image and log saved successfully',
        'log' => $log,
      ], 201);
    }

    return response()->json(['error' => 'Image upload failed'], 500);
  }

  // public function getStaffImageLog(Request $request , $id)
  // {
  //   error_log('hello');
  //   // Validate the request
  //   $validator = Validator::make($request->all(), [
  //     'user_id' => 'required|exists:staff_users,id',
  //   ]);
  //   if ($validator->fails()) {
  //     return response()->json(['error' => $validator->errors()], 422);
  //   }

  //   // Retrieve images for the given user_id
  //   $images = Staff_emergency_logs::where('user_id', $request->$id)
  //     ->get()
  //     ->map(function ($image) {
  //       $image->image_path = url($image->image_path); // Make sure this URL is accessible
  //       return $image;
  //     });

  //   if ($images->isEmpty()) {
  //     return response()->json(['message' => 'No images found for this user'], 404);
  //   }

  //   return response()->json([
  //     'message' => 'Images retrieved successfully',
  //     'images' => $images,
  //   ], 200);
  // }


  public function getAllStaffImageLog(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'start_date' => 'sometimes|nullable|date',
      'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
      'page' => 'sometimes|integer',
      'per_page' => 'sometimes|integer|min:1',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => $validator->errors()], 422);
    }

    $query = Staff_emergency_logs::query();

    if (!empty($request->start_date)) {
      $query->whereDate('created_at', '>=', $request->start_date);
    }

    if (!empty($request->end_date)) {
      $query->whereDate('created_at', '<=', $request->end_date);
    }

    $perPage = $request->input('per_page', 8);

    $images = $query->paginate($perPage);

    $images->getCollection()->transform(function ($image) {
      $image->image_path = url($image->image_path);
      return $image;
    });

    if ($images->isEmpty()) {
      return response()->json(['message' => 'No images found'], 404);
    }

    return response()->json([
      'message' => 'Images retrieved successfully',
      'images' => $images,
    ], 200);
  }


  public function getStaffImageLog(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'start_date' => 'sometimes|nullable|date',
      'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
      'page' => 'sometimes|integer',
      'per_page' => 'sometimes|integer|min:1',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => $validator->errors()], 422);
    }

    $query = Staff_emergency_logs::query();

    $query->where('user_id', $id);

    if (!empty($request->start_date)) {
      $query->whereDate('created_at', '>=', $request->start_date);
    }

    if (!empty($request->end_date)) {
      $query->whereDate('created_at', '<=', $request->end_date);
    }

    $perPage = $request->input('per_page', 8);

    $images = $query->paginate($perPage);

    $images->getCollection()->transform(function ($image) {
      $image->image_path = url($image->image_path);
      return $image;
    });

    return response()->json([
      'message' => 'Images retrieved successfully',
      'images' => $images,
    ], 200);
  }

  //Gallery Log
  public function createGalleryLog(Request $request)
{
    $validator = Validator::make($request->all(), [
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'user_id' => 'required|exists:staff_users,id',
        'description' => 'string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    if ($request->hasFile('image')) {
        $image = $request->file('image');

        try {
            $path = $image->store('gallery_images/' . $request->user_id, 'public');
            $imageUrl = url('storage/' . $path);

            // Create a new gallery log entry
            $log = Staff_emergency_logs::create([
                'user_id' => $request->user_id,
                'image_path' => $imageUrl,
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'Gallery log saved successfully',
                'log' => $log,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Image upload failed'], 500);
        }
    }

    return response()->json(['error' => 'Image upload failed'], 500);
}

  
  // Get All Gallery Logs with Images

  public function getAllGalleryLogs(Request $request)
  {
      // Validate the incoming request
      $validator = Validator::make($request->all(), [
          'start_date' => 'sometimes|nullable|date',
          'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
          'page' => 'sometimes|integer',
          'per_page' => 'sometimes|integer|min:1',
          'name' => 'sometimes|string',
      ]);
  
      if ($validator->fails()) {
          return response()->json(['error' => $validator->errors()], 422);
      }
  
      $query = Staff_emergency_logs::query();
  
      // Apply date filters only if both dates are provided
      if (!empty($request->start_date) && !empty($request->end_date)) {
          $query->whereDate('created_at', '>=', $request->start_date)
                ->whereDate('created_at', '<=', $request->end_date);
      }
  
      // Apply name filter
      if (!empty($request->name)) {
          $query->whereHas('staffUser', function($q) use ($request) {
              $q->where('name', 'like', '%' . $request->name . '%');
          });
      }
  
      $perPage = $request->input('per_page', 8);
      $logs = $query->paginate($perPage);
  
      if ($logs->total() === 0) {
          return response()->json(['message' => 'No images found'], 404);
      }
  
      // Transform the image paths
      $logs->getCollection()->transform(function ($log) {
          $log->image_path = url($log->image_path);
          return $log;
      });
  
      return response()->json([
          'message' => 'Images retrieved successfully',
          'images' => $logs,
      ], 200);
  }
  

public function getGalleryLogById(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'start_date' => 'sometimes|nullable|date',
      'end_date' => 'sometimes|nullable|date|after_or_equal:start_date',
      'page' => 'sometimes|integer',
      'per_page' => 'sometimes|integer|min:1',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => $validator->errors()], 422);
    }

    $query = Staff_emergency_logs::query();

    $query->where('user_id', $id);

    if (!empty($request->start_date)) {
      $query->whereDate('created_at', '>=', $request->start_date);
    }

    if (!empty($request->end_date)) {
      $query->whereDate('created_at', '<=', $request->end_date);
    }

    $perPage = $request->input('per_page', 8);

    $images = $query->paginate($perPage);

    $images->getCollection()->transform(function ($image) {
      $image->image_path = url($image->image_path);
      return $image;
    });

    return response()->json([
      'message' => 'Images retrieved successfully',
      'images' => $images,
    ], 200);
  }
  
} 