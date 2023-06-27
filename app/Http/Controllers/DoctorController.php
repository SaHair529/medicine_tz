<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'last_name' =>              'required|string',
            'first_name' =>             'required|string',
            'phone' =>                  'required|regex:/^\d{10}$/',
            'beginning_work_time' =>    ['required', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/', 'string'],
            'end_work_time' =>          ['required', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/', 'string'],
            'middle_name' =>            'nullable|string',
            'date_of_birth' =>          'nullable|date',
            'email' =>                  'nullable|email'
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);

        $doctor = new Doctor();
        $doctor->last_name = $request->string('last_name');
        $doctor->first_name = $request->string('first_name');
        $doctor->middle_name = $request->string('middle_name');
        $doctor->phone = $request->string('phone');
        $doctor->beginning_work_time = $request->string('beginning_work_time');
        $doctor->end_work_time = $request->string('end_work_time');
        $doctor->date_of_birth = $request->date('date_of_birth');
        $doctor->email = $request->string('email');
        $doctor->save();

        return response()->json(['message' => 'Doctor created successfully'], Response::HTTP_CREATED);
    }
}
