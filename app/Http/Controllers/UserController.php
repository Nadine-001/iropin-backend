<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // $users = User::all();
        // return response()->json($users);

        return new UserCollection(Task::all());
    }

    public function show(Request $request, User $user)
    {
        // return new UserResource($users);

        return new UserCollection(Task::all());
    }

    public function store(Request $request)
    {
        $validated = $request -> validate([
            'password' => 'required|min:8',
        ]);

        $user = User::create($validated);
        return new UserResource($users);

        // return new UserCollection(Task::all());
    }
}
