<?php

namespace Database\Factories;

use App\Models\StaffTimelog;
use App\Models\StaffUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffTimelogFactory extends Factory
{
    protected $model = StaffTimelog::class;

    public function definition()
    {
        return [
            'user_id' => StaffUser::inRandomOrder()->first()->id, 
            'logs' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'type' => $this->faker->randomElement(['Check-In', 'Check-Out']),
        ];
    }
}
