<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider storeSuccessDataProvider
     */
    public function testStore_success($snils, $lastName = null, $firstName = null, $middleName = null, $birthdate = null, $residencePlace = null): void
    {
        $requestData = [
            'last_name' => $lastName,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'snils' => $snils,
            'date_of_birth' => $birthdate,
            'place_of_residence' => $residencePlace
        ];

        if ($lastName === null)
            unset($requestData['last_name']);
        if ($firstName === null)
            unset($requestData['first_name']);
        if ($middleName === null)
            unset($requestData['middle_name']);
        if ($birthdate === null)
            unset($requestData['date_of_birth']);
        if ($residencePlace === null)
            unset($requestData['place_of_residence']);

        $response = $this->postJson('/api/patients/create', $requestData);
        $response->assertStatus(201);

        $this->assertDatabaseHas('patients', $requestData);
    }

    /**
     * @dataProvider storeInvalidDataProvider
     */
    public function testStore_invalidRequestData($snils = null, $lastName = null, $firstName = null, $middleName = null, $birthdate = null, $residencePlace = null)
    {
        $requestData = [
            'last_name' => $lastName,
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'snils' => $snils,
            'date_of_birth' => $birthdate,
            'place_of_residence' => $residencePlace
        ];

        $response = $this->postJson('/api/patients/create', $requestData);
        $response->assertStatus(422);

        $invalidFields = array_keys($requestData);
        if ($snils !== null)
            unset($invalidFields[array_search('snils', $invalidFields)]);
        if ($birthdate === null)
            unset($invalidFields[array_search('date_of_birth', $invalidFields)]);
        if ($residencePlace === null)
            unset($invalidFields[array_search('place_of_residence', $invalidFields)]);
        if ($lastName !== null || $firstName !== null || $middleName !== null) {
            unset($invalidFields[array_search('last_name', $invalidFields)]);
            unset($invalidFields[array_search('first_name', $invalidFields)]);
            unset($invalidFields[array_search('middle_name', $invalidFields)]);
        }

        $response->assertJsonValidationErrors($invalidFields);
    }

    public function storeInvalidDataProvider(): array
    {
        return [
            [ # Пустой запрос

            ],
            [ # Ни один из last_name, first_name, middle_name не заполнен
                '13214314', null, null, null
            ],
            [ # Некорректный формат birthdate
                '123124124', 'last_name', null, null, '11 февраля 1998 года'
            ],
            [ # Незаполнен СНИЛС
                null, null, 'first_name', null, '1998-30-12'
            ]
        ];
    }

    public function storeSuccessDataProvider(): array
    {
        return [
            [ # Все поля заполнены
                '123124124124', 'lastname', 'firstname', 'middleName', '1998-12-30', 'residencePlace'
            ],
            [ # Заполнены только необходимые поля(lastname)
                '12352112', 'lastname'
            ],
            [ # Заполнены только необходимые поля(firstname)
                '12352112', null, 'firstname'
            ],
            [ # Заполнены только необходимые поля(middleName)
                '12352112', null, null, 'middleName'
            ],
        ];
    }
}
