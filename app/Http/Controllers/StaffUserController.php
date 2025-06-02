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
use Illuminate\Support\Facades\DB;

class StaffUserController
{
  public function createStaffUser(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'email|nullable|unique:staff_users|max:255',
      'phone' => 'required|string|max:20|unique:staff_users',
      'mpin' => 'nullable|string|max:6',
      'address' => 'required|string|max:500',
      'status' => 'boolean|nullable',
      'department' => 'nullable|string|max:255',
      'image' => 'nullable|string',
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
        'status' => $request->status ?? 1,
        'department' => $request->department,
        'image' => $request->image,
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
      'name' => 'nullable|string|max:255',
      'email' => 'nullable|email|unique:staff_users,email,' . $id,
      'phone' => 'nullable|string|max:20|unique:staff_users,phone,' . $id,
      'mpin' => 'nullable|string|max:6',
      'address' => 'nullable|string|max:500',
      'status' => 'nullable|boolean',
      'department' => 'nullable|string|max:255',
      'image' => 'nullable|string',
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
      $staffUser->email = $request->input('email', $staffUser->email);
      $staffUser->phone = $request->input('phone', $staffUser->phone);
      $staffUser->mpin = $request->input('mpin', $staffUser->mpin);
      $staffUser->address = $request->input('address', $staffUser->address);
      $staffUser->status = $request->input('status', $staffUser->status);
      $staffUser->department = $request->input('department', $staffUser->department);
      $staffUser->image = $request->input('image', $staffUser->image);
      $staffUser->save();

      return response()->json([
        'success' => true,
        'error' => false,
        'message' => 'Staff User updated successfully',
        'data' => $staffUser
      ], 200);

    } catch (\Throwable $th) {
      Log::error($th);
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

    $staff = $staffQuery->get();

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
        $staffId = $request->id;

        $findStaff = StaffUser::find($staffId);
        if (!$findStaff) {
            return response()->json([
                'success' => 0,
                'error' => 1,
                'message' => 'Staff not found',
                'data' => null
            ], 404);
        }

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;

        $logs = DB::table('staff_timelogs')
            ->where('user_id', $staffId)
            ->when($startDate, fn($q) => $q->where('logs', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('logs', '<=', $endDate))
            ->orderBy('logs')
            ->get()
            ->groupBy(fn($item) => Carbon::parse($item->logs)->format('Y-m-d'));

        $results = [];

        foreach ($logs as $date => $dayLogs) {
            $logEntries = $dayLogs->sortBy('logs')->values();
            $totalWorkSeconds = 0;
            $totalBreakSeconds = 0;
            $lastCheckOut = null;
            $pendingCheckIn = null;
            $punches = [];

            foreach ($logEntries as $entry) {
                $entryTime = Carbon::parse($entry->logs);
                $punches[] = [
                    'type' => $entry->type,
                    'time' => $entryTime->format('H:i:s'),
                    'timestamp' => $entry->logs
                ];
                if ($entry->type === 'check-in') {
                    if ($lastCheckOut !== null) {
                        $breakSeconds = $lastCheckOut->diffInSeconds($entryTime);
                        if ($breakSeconds > 0) {
                            $totalBreakSeconds += $breakSeconds;
                        }
                    }
                    $pendingCheckIn = $entryTime;
                } elseif ($entry->type === 'check-out') {
                    if ($pendingCheckIn !== null) {
                        $workSeconds = $pendingCheckIn->diffInSeconds($entryTime);
                        if ($workSeconds > 0) {
                            $totalWorkSeconds += $workSeconds;
                        }
                        $lastCheckOut = $entryTime;
                        $pendingCheckIn = null;
                    } else {
                        $lastCheckOut = $entryTime;
                    }
                }
            }

            $formattedPunches = array_map(function($punch) {
                $type = str_replace('check-', '', $punch['type']);
                return "{$punch['time']} {$type}";
            }, $punches);

            $results[] = [
                'date' => $date,
                'staff_name' => $findStaff->name,
                'check_in' => optional($logEntries->firstWhere('type', 'check-in'))->logs
                    ? Carbon::parse($logEntries->firstWhere('type', 'check-in')->logs)->format('H:i:s')
                    : null,
                'check_out' => optional($logEntries->where('type', 'check-out')->last())->logs
                    ? Carbon::parse($logEntries->where('type', 'check-out')->last()->logs)->format('H:i:s')
                    : null,
                'punches' => $punches,
                'formatted_punches' => implode(', ', $formattedPunches),
                'total_hours' => $this->formatSecondsToHoursMinutes($totalWorkSeconds),
                'break_hours' => $this->formatSecondsToHoursMinutes($totalBreakSeconds),
            ];
        }

        return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => 'Staff Time Logs',
            'data' => $results,
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

  private function formatSecondsToHoursMinutes($seconds)
  {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
  }

  public function getAllStaffTimelog(Request $request)
  {
    try {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;
        $logs = DB::table('staff_timelogs')
            ->join('staff_users', 'staff_timelogs.user_id', '=', 'staff_users.id')
            ->select(
                'staff_users.id as staff_id',
                'staff_users.name as staff_name',
                DB::raw("DATE(staff_timelogs.logs) as log_date"),
                'staff_timelogs.logs',
                'staff_timelogs.type'
            )
            ->when($startDate, fn($q) => $q->where('logs', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('logs', '<=', $endDate))
            ->orderBy('staff_users.id')
            ->orderBy('staff_timelogs.logs')
            ->get()
            ->groupBy(fn($item) => $item->staff_id . '_' . $item->log_date);
        $results = [];
        foreach ($logs as $dayKey => $dayLogs) {
            $firstLog = $dayLogs->first();
            $logEntries = $dayLogs->sortBy('logs')->values();
            $totalWorkSeconds = 0;
            $totalBreakSeconds = 0;
            $lastCheckOut = null;
            $pendingCheckIn = null;
            $punches = [];
            foreach ($logEntries as $entry) {
                $entryTime = Carbon::parse($entry->logs);
                $punches[] = [
                    'type' => $entry->type,
                    'time' => $entryTime->format('H:i:s'),
                    'timestamp' => $entry->logs
                ];
                if ($entry->type === 'check-in') {
                    if ($lastCheckOut !== null) {
                        $breakSeconds = $lastCheckOut->diffInSeconds($entryTime);
                        if ($breakSeconds > 0) {
                            $totalBreakSeconds += $breakSeconds;
                        }
                    }
                    $pendingCheckIn = $entryTime;
                } elseif ($entry->type === 'check-out') {
                    if ($pendingCheckIn !== null) {
                        $workSeconds = $pendingCheckIn->diffInSeconds($entryTime);
                        if ($workSeconds > 0) {
                            $totalWorkSeconds += $workSeconds;
                        }
                        $lastCheckOut = $entryTime;
                        $pendingCheckIn = null;
                    } else {
                        $lastCheckOut = $entryTime;
                    }
                }
            }
            $formattedPunches = array_map(function($punch) {
                $type = str_replace('check-', '', $punch['type']);
                return "{$punch['time']} {$type}";
            }, $punches);
            $results[] = [
                'date' => $firstLog->log_date,
                'staff_name' => $firstLog->staff_name,
                'check_in' => optional($dayLogs->firstWhere('type', 'check-in'))->logs
                    ? Carbon::parse($dayLogs->firstWhere('type', 'check-in')->logs)->format('H:i:s')
                    : null,
                'check_out' => optional($dayLogs->where('type', 'check-out')->last())->logs
                    ? Carbon::parse($dayLogs->where('type', 'check-out')->last()->logs)->format('H:i:s')
                    : null,
                'punches' => $punches,
                'formatted_punches' => implode(', ', $formattedPunches),
                'total_hours' => $this->formatSecondsToHoursMinutes($totalWorkSeconds),
                'break_hours' => $this->formatSecondsToHoursMinutes($totalBreakSeconds),
            ];
        }
        return response()->json([
            'success' => 1,
            'error' => 0,
            'message' => 'Staff Time Logs',
            'logs' => $results,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => 0,
            'error' => 1,
            'message' => $e->getMessage(),
            'logs' => [],
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
    dd($query);
    exit;

    if (!empty($request->start_date)) {
      $query->whereDate('created_at', '>=', $request->start_date);
    }

    if (!empty($request->end_date)) {
      $query->whereDate('created_at', '<=', $request->end_date);
    }

    $perPage = $request->input('per_page', 8);

    $images = $query->paginate($perPage);

    $images->getCollection()->transform(function ($image) {
      return [
        'id' => $image->id,
        'user_id' => $image->user_id,
        'image' => $image->image_path,
        'description' => $image->description,
        'created_at' => $image->created_at,
      ];
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