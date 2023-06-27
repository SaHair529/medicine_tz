<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Stringable;

/**
 * @property mixed|string $last_name
 * @property mixed|string $first_name
 * @property mixed|string $middle_name
 * @property mixed|string $phone
 * @property mixed|string $beginning_work_time
 * @property mixed|string $end_work_time
 * @property mixed|\DateTime $date_of_birth
 * @property mixed|string $email
 * @method static where(string $string, string $string1, Stringable $doctorId)
 */
class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'phone',
        'beginning_work_time',
        'end_work_time',
        'date_of_birth',
        'email'
    ];
}
