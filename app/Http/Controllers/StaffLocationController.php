<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\StaffLocation;
use Illuminate\Http\Request;
use MongoDB\BSON\UTCDateTime;

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
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date',
            ]);
    
            // Build the query based on the filters
            $match = ['uuid' => $uuid];
            
            if (isset($validatedData['start_date']) && !empty($validatedData['start_date'])) {
                $startDate = Carbon::parse($validatedData['start_date'])->startOfDay();
                $match['created_at']['$gte'] = new UTCDateTime($startDate->getTimestamp() * 1000);
            }
    
            if (isset($validatedData['end_date']) && !empty($validatedData['end_date'])) {
                $endDate = Carbon::parse($validatedData['end_date'])->endOfDay();
                $match['created_at']['$lte'] = new UTCDateTime($endDate->getTimestamp() * 1000);
            }
    
            if (isset($validatedData['start_date']) && isset($validatedData['end_date'])) {
                $startDate = Carbon::parse($validatedData['start_date'])->startOfDay();
                $endDate = Carbon::parse($validatedData['end_date'])->endOfDay();
                $match['created_at'] = [
                    '$gte' => new UTCDateTime($startDate->getTimestamp() * 1000),
                    '$lte' => new UTCDateTime($endDate->getTimestamp() * 1000),
                ];
            }
    
            // Define the aggregation pipeline
            $pipeline = [
                ['$match' => $match],
                ['$group' => [
                    '_id' => [
                        'latitude' => '$latitude',
                        'longitude' => '$longitude'
                    ],
                    'uuid' => ['$first' => '$uuid'],
                    'created_at' => ['$first' => '$created_at']
                ]],
                ['$project' => [
                    'latitude' => '$_id.latitude',
                    'longitude' => '$_id.longitude',
                    'uuid' => 1,
                    'created_at' => 1
                ]]
            ];
            // Execute the aggregation
            $locations = $this->staffLocation->findWithQuery($pipeline);
            return response()->json([
                'message' => 'Locations retrieved successfully',
                'locations' => $locations,
            ], 200);
        } catch (\Exception $e) {
            // Log the error and return a failure response
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
            // Validate the request data
            $validatedData = $request->validate([
                'uuid' => 'required|string',
                'name' => 'required|string',
                'latitude' => 'required|string',
                'longitude' => 'required|string',
            ]);

            // Insert the location into the database
            $result = $this->staffLocation->insertStaffLocation($validatedData);

            // Return a response
            return response()->json([
                'message' => 'Location added successfully',
                'inserted_id' => $result->getInsertedId(),
            ], 201);
        } catch (\Exception $e) {
            // Log the error and return a failure response
            \Log::error('Error adding location: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to add location',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
