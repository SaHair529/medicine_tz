<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AppointmentFactory extends Factory
{
    private Carbon $startTime;
    public function __construct($count = null, ?Collection $states = null, ?Collection $has = null, ?Collection $for = null, ?Collection $afterMaking = null, ?Collection $afterCreating = null, $connection = null, ?Collection $recycle = null)
    {
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection, $recycle);
        $this->startTime = Carbon::today()->addHours(9);
    }

    public function definition(): array
    {
        return [
            'doctor_id' => $this->getValidDoctorId(),
            'patient_id' => $this->getValidPatientId(),
        ];
    }

    public function getValidDoctorId()
    {
        $doctor = Doctor::all()->first();

        return $doctor ? $doctor->id : Doctor::factory()->create()->id;
    }

    public function getValidPatientId()
    {
        $patient = Doctor::all()->first();

        return $patient ? $patient->id : Doctor::factory()->create()->id;
    }

    public function configure(): AppointmentFactory
    {
        return $this->afterMaking(function (Appointment $appointment) {
            $appointment->start_time = $this->startTime->format('Y-m-d H:i:s');
            $appointment->end_time = Carbon::parse($this->startTime)->addMinutes(30)->format('Y-m-d H:i:s');

            $this->startTime->addMinutes(31);
        });
    }
}
