<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Stringable;

/**
 * @property mixed|integer $doctor_id
 * @property mixed|integer $patient_id
 * @property mixed|\DateTime $start_time
 * @property mixed|\DateTime $end_time
 * @method static where(string $key, string $operator, Stringable $value)
 */
class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'patient_id', 'start_time', 'end_time'];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public static function isAppointmentTimeFree($doctorId, $startTime, $endTime): bool
    {
        return !self::where('doctor_id', '=', $doctorId)
            ->where(function ($qb) use ($startTime, $endTime) {
                $qb->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($query) use ($startTime, $endTime) {
                        $query->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })->exists();
    }
}
