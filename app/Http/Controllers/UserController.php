<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['biodata', 'address', 'office'])->get();

        $user_data = $users->map(function ($user) {
            return [
                'user' => $user->only('id', 'email', 'role_id', 'is_active', 'membership_number'),
                'biodata' => $user->biodata ? $user->biodata->name : null,
                'address' => $user->address ? $user->address->regency_city : null,
                'office' => $user->office ? $user->office->office_name : null,
            ];
        });

        return response()->json(['user_data' => $user_data]);
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'user' => $user->only('id', 'email', 'role_id', 'is_active'),
            'biodata' => $user->biodata,
            'address' => $user->address,
            'education' => $user->education,
            'office' => $user->office,
        ]);
    }

    public function activateUser(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);

        // generate nomor anggota
        if (!$user->membership_number) {

            $latest_membership_number = User::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->where('is_active', 1)
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

        $user->is_active = true;
        $user->save();

        return response()->json([
            'message' => 'Succesfully activate user',
            'access_token' => $membershipNumber,
        ]);
    }

    public function deactivateUser(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);
        $user->is_active = false;
        $user->save();

        return response()->json([
            'message' => 'Succesfully deactivate user'
        ]);
    }
}
