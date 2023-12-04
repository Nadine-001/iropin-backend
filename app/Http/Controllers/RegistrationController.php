<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Biodata;
use App\Models\Document;
use App\Models\Education;
use App\Models\Office;
use App\Models\Registration;
use App\Models\RegistrationFormDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
            'NIK' => 'required|max:16|unique:biodatas',
            'birthplace' => 'required',
            'birthdate' => 'required',
            'gender' => 'required',
            'STR_number' => 'required',
            'publish_date' => 'required',
            'exp_date' => 'required',
            'address' => 'required|string',
            'institution' => 'required|string',
            'office_name' => 'required',
            'employment_status' => 'required',
            'office_regency_city' => 'required',
            'files.*' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $biodata = Biodata::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'prefix' => $request->prefix,
                'sufix' => $request->sufix,
                'NIK' => $request->NIK,
                'birthplace' => $request->birthplace,
                'birthdate' => $request->birthdate,
                'gender' => $request->gender,
                'religion' => $request->religion,
                'mobile_phone' => $request->mobile_phone,
                'whatsapp_number' => $request->whatsapp_number,
                'STR_number' => $request->STR_number,
                'publish_date' => $request->publish_date,
                'exp_date' => $request->exp_date,
            ]);

            $address = Address::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'province' => 'Jawa Tengah',
                'regency_city' => $request->regency_city,
                'telephone' => $request->telephone,
                'zip_code' => $request->zip_code,
            ]);

            $education = Education::create([
                'user_id' => $user->id,
                'institution' => $request->institution,
                'study' => $request->study,
            ]);

            $office = Office::create([
                'user_id' => $user->id,
                'office_regency_city' => $request->office_regency_city,
                'office_name' => $request->office_name,
                'office_address' => $request->office_address,
                'employment_status' => $request->employment_status,
                'position' => $request->position,
                'office_phone' => $request->office_phone,
            ]);

            $registration = Registration::create([
                'user_id' => $user->id,
                'status' => 0,
                'note' => $request->note
            ]);

            $count = 1;
            $files = [];
            if ($request->file('files')) {
                foreach($request->file('files') as $key => $value)
                {
                    $key = "doc0_" . $count;
                    $ext = $value->getClientOriginalExtension();
                    $file_name = time() . " - " . $value->getClientOriginalName();
                    $file_name = str_replace(' ', '', $file_name);
                    $path = asset("uploads/registrations".$file_name);
                    $value->move(public_path('uploads/registrations'), $file_name);

                    $document = Document::create([
                        'path' => $path,
                        'ext' => $ext,
                        'file_name' => $file_name,
                        'is_checked' => 0,
                    ]);

                    $files[$key] = $document;

                    $detail = RegistrationFormDetail::create([
                        'registration_id' => $registration->id,
                        'key' => $key,
                        'val' => $file_name,
                        'document_id' => $document->id,
                    ]);

                    $count++;
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'register failed',
                'errors' => $th->getMessage()
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration success! Below here is your data :',
            'data' => $user,
            'biodata' => $biodata,
            'address' => $address,
            'education' => $education,
            'office' => $office,
            'files' => $files,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function registrationList(Request $request) {
        $users = User::where('role_id', '<', 50)
            ->with(['biodata', 'address', 'office', 'registration'])
            ->get();

        try {
            $user_data = $users->map(function ($registration) {
                return [
                    'registration' => [
                        'created_at' => $registration->created_at->toDateString(),
                        'name' => $registration->biodata ? $registration->biodata->name : null,
                        'NIK' => $registration->biodata ? $registration->biodata->nik : null,
                        'status' => $registration->registration->status,
                    ],
                ];
            });
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get registration list',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'registration' => $user_data
        ]);
    }
}
