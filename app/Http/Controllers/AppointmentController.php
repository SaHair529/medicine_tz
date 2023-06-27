<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppointmentResource;
use App\Models\Doctor;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function index(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $query = Appointment::query();
        $validator = Validator::make($request->all(), [
            'doctor_fullname' => ['nullable', 'string',
                function ($attribute, $value, $fail) {
                    $wordCount = preg_match_all('/\p{L}+/u', $value);
                    if ($wordCount < 2 || $wordCount > 3) {
                        $fail("The $attribute must have 2 or 3 words");
                    }
                }],
            'patient_fullname' => 'nullable|string',
            'date_interval' => ['nullable', 'regex:/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2})?\|(\d{4}-\d{2}-\d{2} \d{2}:\d{2})?$|^(\d{4}-\d{2}-\d{2})?\|(\d{4}-\d{2}-\d{2})?$/',
                function($attr, $val, $fail) {
                    try {
                        [$startDate, $endDate] = explode('|', $val);
                        $startDate = Carbon::parse($startDate);
                        $endDate = Carbon::parse($endDate);

                        if ($startDate > $endDate)
                            $fail('The end_date must be higher that start_date');
                    }
                    catch (InvalidFormatException | \ErrorException) {
                        $fail('The date_interval must be in the format YYYY-MM-DD HH:MM|YYYY-MM-DD HH:MM or YYYY-MM-DD|YYYY-MM-DD.');
                    }
                }
            ],
        ], [
            'date_interval.regex' => 'The date_interval must be in the format YYYY-MM-DD HH:MM|YYYY-MM-DD HH:MM or YYYY-MM-DD|YYYY-MM-DD.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if ($request->has('doctor_fullname')) {
            $doctorFullnameArray = explode(' ', $request->string('doctor_fullname'));
            $query->whereHas('doctor', function ($q) use ($doctorFullnameArray) {
                $q->whereIn('last_name', $doctorFullnameArray);
                $q->whereIn('first_name', $doctorFullnameArray);
                if (count($doctorFullnameArray) === 3)
                    $q->whereIn('middle_name', $doctorFullnameArray);
            });
        }

        if ($request->has('patient_fullname')) {
            $fullnameArray = explode(' ', $request->string('patient_fullname'));
            $query->whereHas('patient', function ($q) use ($fullnameArray) {
                foreach ($fullnameArray as $fullnameChunk) {
                    $q->where(function ($query) use ($fullnameChunk) {
                        $query->where('last_name', '=', $fullnameChunk)
                            ->orWhere('first_name', '=', $fullnameChunk)
                            ->orWhere('middle_name', '=', $fullnameChunk);
                    });
                }
            });
        }

        if ($request->has('date_interval')) {
            $dateInterval = $request->string('date_interval');
            $query->whereBetween('start_time', explode('|', $dateInterval));
        }

        $query->orderBy('start_time');
        $appointments = $query->paginate(10);

        return AppointmentResource::collection($appointments);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|numeric|exists:doctors,id',
            'patient_id' => 'required|numeric|exists:patients,id',
            'appointment_date' => 'required|date_format:Y-m-d',
            'appointment_time' => 'required|date_format:H:i'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doctorId = $request->string('doctor_id');
        $patientId = $request->string('patient_id');
        $appointmentDate = $request->string('appointment_date');
        $appointmentTime = $request->string('appointment_time');

        $appointmentStartTime = Carbon::parse($appointmentDate.' '.$appointmentTime);
        $appointmentEndTime = $appointmentStartTime->copy()->addMinutes(30);

        if (!Appointment::isAppointmentTimeFree($doctorId, $appointmentStartTime, $appointmentEndTime))
            return response()->json(['error' => 'This appointment time is not available.'], 422);

        $doctor = Doctor::where('id', '=', $doctorId)->first();

        $workingStartTime = Carbon::parse($appointmentDate . ' ' . $doctor->beginning_work_time);
        $workingEndTime = Carbon::parse($appointmentDate . ' ' . $doctor->end_work_time);
        if ($workingStartTime > $workingEndTime)
            $workingStartTime->subDay();

        if ($appointmentStartTime < $workingStartTime || $appointmentEndTime > $workingEndTime) {
            return response()->json(['error' => 'This appointment time is outside of the doctor\'s working hours.'], 422);
        }

        $appointment = new Appointment();
        $appointment->doctor_id = $doctorId;
        $appointment->patient_id = $patientId;
        $appointment->start_time = $appointmentStartTime;
        $appointment->end_time = $appointmentEndTime;
        $appointment->save();

        return response()->json(['message' => 'Appointment created successfully.'], 201);
    }
}
