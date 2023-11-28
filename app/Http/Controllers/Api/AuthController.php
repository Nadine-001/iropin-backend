<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'login success',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'string|max:255|unique:users,email,' . $user->id,
            'password' => 'string|min:8',
            'NIK' => 'int|digits:16',
            'address' => 'string',
            'regency_city' => 'string',
            'institution' => 'string',
            'office_address' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        DB::beginTransaction();
        try {
            $user->update([
                'email' => $request->email,
            ]);

            $user->biodata->update([
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

            $user->address->update([
                'address' => $request->address,
                'regency_city' => $request->regency_city,
                'telephone' => $request->telephone,
            ]);

            $user->education->update([
                'institution' => $request->institution,
                'study' => $request->study,
            ]);

            $user->office->update([
                'office_name' => $request->office_name,
                'office_address' => $request->office_address,
                'employment_status' => $request->employment_status,
                'position' => $request->position,
                'office_phone' => $request->office_phone,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'update failed',
                'errors' => $th->getMessage()
            ]);
        }

        return response()->json([
            'message' => 'updated succesfully'
        ]);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json([
            'message' => 'logout success'
        ]);
    }
}
