<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\RegistrationFormDetail;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getUsers(Request $request) {
        $loginUser = Auth::user();
        $users = User::where('role_id', '<', $loginUser->role_id)
            ->with(['biodata', 'address', 'office', 'registration'])
            ->get();

        try {
            $user_data = $users->map(function ($user) {
                return [
                    'user' => $user->only('id', 'email', 'role_id', 'membership_number'),
                    'name' => $user->biodata ? $user->biodata->name : null,
                    'office_regency_city' => $user->office ? $user->office->office_regency_city : null,
                    'office_name' => $user->office ? $user->office->office_name : null,
                    'status' => $user->registration ? $user->registration->status : null,
                ];
            });
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get user list',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json(['user_data' => $user_data]);
    }

    function getUserDetail(Request $request, $user_id) {
        $user = User::findOrFail($user_id);

        $registration = Registration::where('user_id', $user->id)->firstOrFail();

        $registration_detail = RegistrationFormDetail::where('registration_id', $registration->id)
            ->with('document')
            ->first();

        try {
            $document_data = [
                'document_name' => $registration_detail->val,
                'document_path' => $registration_detail->document->path,
                'key' => $registration_detail->key,
            ];

            return response()->json([
                'email' => $user->email,
                'name' => $user->biodata->name,
                'prefix' => $user->biodata->prefix,
                'sufix' => $user->biodata->sufix,
                'NIK' => $user->biodata->NIK,
                'birthplace' => $user->biodata->birthplace,
                'birthdate' => $user->biodata->birthdate,
                'gender' => $user->biodata->gender,
                'religion' => $user->biodata->religion,
                'mobile_phone' => $user->biodata->mobile_phone,
                'whatsapp_number' => $user->biodata->whatsapp_number,
                'STR_number' => $user->biodata->STR_number,
                'publish_date' => $user->biodata->publish_date,
                'exp_date' => $user->biodata->exp_date,
                'address' => $user->address->address,
                'province' => $user->address->province,
                'regency_city' => $user->address->regency_city,
                'telephone' => $user->address->telephone,
                'zip_code' => $user->address->zip_code,
                'institution' => $user->education->institution,
                'study' => $user->education->study,
                'status' => $registration->status,
                'document_data' => $document_data,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get user detail',
                'errors' => $th->getMessage()
            ]);
        }
    }

    function validateRegistration(Request $request, $user_id) {
        $user = User::findOrFail($user_id);

        $registration = Registration::where('user_id', $user->id)->firstOrFail();

        try {
            $checked = $request->input('checked');
            $checked_decode = json_decode($checked, true);

            if ($checked_decode === null) {
                throw new Exception("please provide a valid json");
            }

            foreach ($checked_decode as $key => $is_checked) {
                $registration_detail = RegistrationFormDetail::where('registration_id', $registration->id)
                    ->where('key', $key)
                    ->with('document')
                    ->first();

                $document = $registration_detail->document;
                $document->update(['is_checked' => $is_checked]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to checklist the documents',
                'errors' => $th->getMessage()
            ]);
        }

        try {
            $user->status = 1;          // sudah diaktivasi
            $registration->status = 1;  // sudah diaktivasi

            // generate membership number
            if (!$user->membership_number) {

                $latest_membership_number = User::whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->where('status', 1)
                    ->whereNotNull('membership_number')
                    ->orderBy('membership_number', 'desc')
                    ->first();

                $get_number = $latest_membership_number ? explode('-', $latest_membership_number->membership_number)[2] : 0;
                $last_number = intval($get_number);

                $membershipNumber = 'CBD-' . now()->format('Ymd') . '-' . ($last_number + 1);

                $user->membership_number = $membershipNumber;
            } else {
                $membershipNumber = $user->membership_number;
            }

            $user->save();
            $registration->save();
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to change user status',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'message' => 'approve user activation success',
            'access_token' => $membershipNumber,
        ]);
    }

    function declineRegistration(Request $request, $user_id) {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::findOrFail($user_id);
        $registration = Registration::where('user_id', $user->id)->first();

        try {
            $checked = $request->input('checked');
            $checked_decode = json_decode($checked, true);

            if ($checked_decode === null) {
                throw new Exception("please provide a valid json");
            }

            if (!$registration) {
                return response()->json([
                    "message" => "registration not found"
                ], 404);
            }

            foreach ($checked_decode as $key => $is_checked) {
                $registration_detail = RegistrationFormDetail::where('registration_id', $registration->id)
                    ->where('key', $key)
                    ->with('document')
                    ->first();

            $document = $registration_detail->document;
            $document->update(['is_checked' => $is_checked]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to checklist the documents',
                'errors' => $th->getMessage()
            ]);
        }

        try {
            $registration->update([
                'status' => 2,
                'note' => $request->note
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to change user status',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'message' => 'deny user activation success'
        ]);
    }

    public function userList(Request $request) {
        $loginUser = Auth::user();
        $users = User::select('id', 'email', 'role_id')
            ->where('role_id', '<', $loginUser->role_id)
            ->get();

        return response()->json($users);
    }

    public function addUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'role_id' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $newUser = User::create([
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => $request->password,
            'status' => 1,
        ]);

        if (!$newUser) {
            return response()->json([
                "message" => "failed add user"
            ], 500);
        }

        return response()->json($newUser);
    }

    public function updateUser(Request $request, $user_id) {
        $user = User::findOrFail($user_id);

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|max:255',
            'role_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $updated = $user->update([
            'email' => $request->email,
            'role_id' => $request->role_id,
        ]);

        if ($request->password) {
            $updated = $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        if (!$updated) {
            return response()->json([
                "message" => "failed update user"
            ], 500);
        }

        return response()->json([
            'message' => 'user updated succesfully',
            'user_data' => $user->only('id', 'email', 'role_id', 'status')
        ]);
    }

    public function deleteUser(Request $request, $user_id) {
        $user = User::findOrFail($user_id);
        $deleted = $user->delete();

        if (!$deleted) {
            return response()->json([
                "message" => "failed delete user"
            ], 500);
        }

        return response()->json([
            "message" => "user deleted successfully"
        ]);
    }

    public function profile(Request $request) {
        try {
            $user = User::findOrFail(Auth::user()->id);

            return response()->json([
                'user' => $user->only('id', 'email', 'role_id', 'status', 'membership_number'),
                'biodata' => $user->biodata,
                'address' => $user->address,
                'education' => $user->education,
                'office' => $user->office,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed to get the profile',
                'errors' => $th->getMessage()
            ]);
        }
    }
}
