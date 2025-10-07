<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\StaffLocation;
use Illuminate\Http\Request;

class StaffLocationController
{
    protected $staffLocation;

    public function __construct(StaffLocation $staffLocation)
    {
        $this->staffLocation = $staffLocation;
    }

    public function getStaffLocation(Request $request, $uuid)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'start_date' => 'sometimes|date_format:Y-m-d',
                'end_date' => 'sometimes|date_format:Y-m-d',
            ]);
    
            // Build the query based on the filters
            $query = $this->staffLocation->newQuery()->where('uuid', $uuid);
            
            if (isset($validatedData['start_date']) && isset($validatedData['end_date'])) {
                $startDate = Carbon::parse($validatedData['start_date'])->startOfDay();
                $endDate = Carbon::parse($validatedData['end_date'])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);

            } elseif (isset($validatedData['start_date'])) {
                $startDate = Carbon::parse($validatedData['start_date'])->startOfDay();
                $query->where('created_at', '>=', $startDate);

            } elseif (isset($validatedData['end_date'])) {
                $endDate = Carbon::parse($validatedData['end_date'])->endOfDay();
                $query->where('created_at', '<=', $endDate);
            }
    
            // Define the aggregation pipeline
            $locations = $query
                ->select('uuid')
                ->selectRaw('latitude, longitude, MIN(created_at) as first_seen')
                ->groupBy('latitude', 'longitude', 'uuid')
                ->orderBy('first_seen')
                ->get();

            return response()->json([
                'message' => 'Locations retrieved successfully',
                'locations' => $locations,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error retrieving locations: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve locations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
        
    
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'uuid' => 'required|string',
                'name' => 'required|string',
                'latitude' => 'required|numeric', 
                'longitude' => 'required|numeric',
            ]);

            $newLocation = $this->staffLocation->create($validatedData);

            return response()->json([
                'message' => 'Location added successfully',
                'id' => $newLocation->id, 
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error adding location: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to add location',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
