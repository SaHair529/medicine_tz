<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed|string $last_name
 * @property mixed|string $first_name
 * @property mixed|string $middle_name
 * @property mixed|string $snils
 * @property mixed|\DateTime $date_of_birth
 * @property mixed|string $place_of_residence
 */
class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'snils',
        'date_of_birth',
        'place_of_residence'
    ];
}
