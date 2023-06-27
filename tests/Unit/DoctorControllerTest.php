<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider storeInvalidDataProvider
     */
    public function testStore_invalidRequestData($phone = null, $beginningWorkTime = null, $endWorkTime = null, $birthdate = null, $email = null): void
    {
        $requestData = [
            'phone' => $phone,
            'beginning_work_time' => $beginningWorkTime,
            'end_work_time' => $endWorkTime,
            'date_of_birth' => $birthdate,
            'email' => $email
        ];

        $response = $this->postJson('/api/doctors/create', $requestData);
        $response->assertStatus(422);

        $invalidFields = [
            'last_name', 'first_name',
            'phone', 'beginning_work_time',
            'end_work_time'
        ];
        if ($birthdate !== null)
            $invalidFields[] = 'date_of_birth';
        if ($email !== null)
            $invalidFields[] = 'email';

        $response->assertJsonValidationErrors($invalidFields);
    }

    /**
     * @dataProvider storeSuccessDataProvider
     */
    public function testStore_success($lastName, $firstName, $phone, $begin, $end, $middleName = '', $dateOfBirth = null, $email = ''): void
    {
        $requestData = [
            'last_name' => $lastName,
            'first_name' => $firstName,
            'phone' => $phone,
            'beginning_work_time' => $begin,
            'end_work_time' => $end,
            'middle_name' => $middleName,
            'date_of_birth' => $dateOfBirth,
            'email' => $email,
        ];

        $response = $this->postJson('/api/doctors/create', $requestData);
        $response->assertStatus(201);

        $this->assertDatabaseHas('doctors', $requestData);
    }

    private function storeSuccessDataProvider(): array
    {
        return [
            [ # Все поля заполнены
                'last_name', 'first_name', '9289999999', '10:00', '18:00', 'middle_name', '1998-12-30', 'test@mail.ru'
            ],
            [ # Заполнены только необходимые поля
                'last_name', 'first_name', '9289999999', '10:00', '18:00'
            ],
        ];
    }

    private function storeInvalidDataProvider(): array
    {
        return [
            [ # Пустой запрос

            ],
            [ # Слишком длинный номер
                '+79992221100'
            ],
            [ # Неверный формат beginning_work_time и end_work_time
                null, '09:60', '24:00'
            ],
            [ # Неверный формат birthdate
                null, null, null, '11081998'
            ],
            [ # Неверный формат email
                null, null, null, null, 'mail.ru'
            ]
        ];
    }
}
