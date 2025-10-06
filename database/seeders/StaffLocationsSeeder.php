<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\StaffUser;
use App\Models\StaffLocation;

class StaffLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $staffUsers = StaffUser::take(10)->get(['uuid', 'name']); 
        $latitude = 23.130435;
        $longitude = 72.585212;

        foreach ($staffUsers as $staffUser) {
            StaffLocation::create([
                'uuid'      => (string) $staffUser->uuid,
                'name'      => $staffUser->name,
                'latitude'  => $latitude,
                'longitude' => $longitude,
            ]);
        }
    }
}

