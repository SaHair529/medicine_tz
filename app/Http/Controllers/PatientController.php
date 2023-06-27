<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class PatientController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'last_name' => 'required_without_all:first_name,middle_name|string',
            'first_name' => 'required_without_all:last_name,middle_name||string',
            'middle_name' => 'required_without_all:first_name,last_name|string',
            'snils' => 'required|string',
            'date_of_birth' => 'nullable|date',
            'place_of_residence' => 'nullable|string'
        ]);

        if ($validator->fails())
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);

        $patient = new Patient();
        $patient->last_name = $request->string('last_name');
        $patient->first_name = $request->string('first_name');
        $patient->middle_name = $request->string('middle_name');
        $patient->snils = $request->string('snils');
        $patient->date_of_birth = $request->date('date_of_birth');
        $patient->place_of_residence = $request->string('place_of_residence');
        $patient->save();

        return response()->json(['message' => 'Patient created successfully'], Response::HTTP_CREATED);
    }
}
