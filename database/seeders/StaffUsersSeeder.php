<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaffUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 100; $i++) {
            StaffUser::create([
                'name' => 'Staff User ' . $i,
                'email' => 'staffuser' . $i . '@example.com',
                'phone' => '12345678' . $i,
                'mpin' => bcrypt('password' . $i),
                'address' => 'Address ' . $i,
                'status' => $i % 2 == 0,
            ]);
        }
    }
}
