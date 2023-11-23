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
        $users = User::all();
        return response()->json($users);
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
            $registration_count = User::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->count();

            $membershipNumber = 'CBD-' . now()->format('Ymd') . '-' . ($registration_count + 1);
        } else {
            return response()->json([
                'message' => 'Akun sudah diaktifkan'
            ]);
        }

        $user->is_active = true;
        $user->membership_number = $membershipNumber;
        $user->save();

        return response()->json([
            'message' => 'Akun berhasil diaktifkan'
        ]);
    }

    public function deactivateUser(Request $request, $user_id)
    {
        $user = User::findOrFail($user_id);
        $user->is_active = false;
        $user->save();

        return response()->json([
            'message' => 'Akun berhasil dinonaktifkan'
        ]);
    }
}
