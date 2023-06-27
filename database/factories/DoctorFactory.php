<?php

namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'last_name' => $this->faker->lastName,
            'first_name' => $this->faker->firstName,
            'middle_name' => $this->faker->firstName,
            'phone' => $this->faker->phoneNumber,
            'beginning_work_time' => '09:00',
            'end_work_time' => '18:00',
            'date_of_birth' => $this->faker->date(),
            'email' => $this->faker->email
        ];
    }
}
