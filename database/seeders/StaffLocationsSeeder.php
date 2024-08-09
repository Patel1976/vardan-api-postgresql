<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\StaffUser;
use MongoDB\Client as MongoClient;

class StaffLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $mongoClient = new MongoClient('mongodb://localhost:27017');

        $mongoDatabase = $mongoClient->selectDatabase('staffCluster');
        $staffLocationsCollection = $mongoDatabase->staff_locations;

        $staffUsers = StaffUser::take(10)->get(['uuid', 'name']); 

        foreach ($staffUsers as $staffUser) {
            $staffLocationsCollection->insertOne([
                'uuid' => (string) $staffUser->uuid,
                'name' => $staffUser->name,
                'location' => [
                    'latitude' => 23.130435,
                    'longitude' => 72.585212,
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }
    }
}

