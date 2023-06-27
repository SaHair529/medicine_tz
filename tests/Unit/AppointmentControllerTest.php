<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Faker\Factory;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Faker\Factory as Faker;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppointmentControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected Doctor $doctor;
    protected Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->doctor = Doctor::factory()->create();

        $this->patient = Patient::factory()->create();
        Appointment::factory()->count(20)->create();
    }

    /**
     * @dataProvider storeInvalidDataDataProvider
     */
    public function testStore_invalidRequestData($doctorId = null, $patientId = null, $appointmentDate = null, $appointmentTime = null)
    {
        $requestData = [
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime
        ];

        $response = $this->postJson('/api/appointments/create', $requestData);
        $response->assertStatus(422);

        $invalidFields = array_keys($requestData);
        $response->assertJsonValidationErrors($invalidFields);
    }

    /**
     * @dataProvider storeSuccessDataProvider
     */
    public function testStore_success($appointmentDate, $appointmentTime)
    {
        $requestData = [
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime
        ];

        $response = $this->postJson('/api/appointments/create', $requestData);
        $response->assertStatus(201);

        $startTime = Carbon::parse($appointmentDate.' '.$appointmentTime);
        $dataForCheck = [
            'doctor_id' => $requestData['doctor_id'],
            'patient_id' => $requestData['patient_id'],
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addMinutes(30)
        ];

        $this->assertDatabaseHas('appointments', $dataForCheck);
    }

    /**
     * @dataProvider indexInvalidDataDataProvider
     */
    public function testIndex_invalidRequestData($doctorFullname = null, $dateInterval = null)
    {
        $requestData = [
            'doctor_fullname' => $doctorFullname,
            'date_interval' => $dateInterval
        ];

        $response = $this->postJson('/api/appointments', $requestData);
        $response->assertStatus(400);

        if ($doctorFullname === null)
            unset($requestData['doctor_fullname']);
        if ($dateInterval === null)
            unset($requestData['date_interval']);
        $invalidFields = array_keys($requestData);

        $response->assertJsonValidationErrors($invalidFields);
    }

    /** @dataProvider indexSuccessDataProvider */
    public function testIndex_success($requestData)
    {
        $response = $this->postJson('/api/appointments', $requestData);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'doctor',
                    'patient',
                    'appointment_time'
                ]
            ]
        ]);

        $expectedCount = 10;
        if (isset($requestData['doctor_fullname']) || isset($requestData['patient_fullname']))
            $expectedCount = 0;
        if (isset($requestData['date_interval']))
            $expectedCount = 6;

        $appointments = $response->json('data');
        $this->assertCount($expectedCount, $appointments);
    }

    public function indexSuccessDataProvider(): array
    {
        $today = Carbon::today()->addHours(9);

        return [
            [[]],
            [[
                'doctor_fullname' => 'doc doc'
            ]],
            [[
                'patient_fullname' => 'pat pat'
            ]],
            [[
                'date_interval' => "{$today->format('Y-m-d H:i')}|{$today->addHours(3)->format('Y-m-d H:i')}"
            ]]
        ];
    }

    public function indexInvalidDataDataProvider(): array
    {
        $faker = Factory::create();

        return [
            [ # Меньше двух слов в doctor_fullname
                'Иванов'
            ],
            [ # Больше двух слов в doctor_fullname
                $faker->name.' '.$faker->name.' '.$faker->name.' '.$faker->name
            ],
            [ # Неверный формат date_interval
                null, '2022-12-30'
            ],
            [ # Конечная дата ниже начальной
                null, '2022-12-30|2022-12-29'
            ]
        ];
    }

    public function storeSuccessDataProvider(): array
    {
        $result = [];

        $faker = Faker::create();
        $appointmentDate = $faker->date;
        $appointmentTime = Carbon::parse('09:00');

        for ($i = 0; $i < 30; $i++) {
            $result[] = [$appointmentDate, $appointmentTime->copy()->addMinutes(31)->format('H:i')];
        }

        return $result;
    }

    public function storeInvalidDataDataProvider(): array
    {
        $faker = Faker::create();

        return [
            [ # Пустой запрос

            ],
            [ # Невалидный формат doctor_id и patient_id
                $faker->word, $faker->word
            ],
            [ # id несозданных doctor и patient
                0, 0
            ],
            [ # невалидный формат даты
                null, null, '11 август 1998'
            ],
            [ # невалидный формат времени
                null, null, null, '12:00:00'
            ],
            [ # Всё вместе)
                $faker->word, $faker->word, '11 август 1998', '12:00:00'
            ]
        ];
    }
}
