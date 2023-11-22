<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
        return response()->json($users);
    }

    public function profile(Request $request)
    {
        return $request->user();
    }
}
