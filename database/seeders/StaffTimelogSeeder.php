<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StaffTimelog;

class StaffTimelogSeeder extends Seeder
{
    public function run()
    {
        StaffTimelog::factory()->count(1000)->create();
    }
}
