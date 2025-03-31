<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminControlsController extends Controller
{
    public function admin_controls()
    {
        $users = User::all(); // Get users from database
        return view('admin_controls', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'id_number' => 'required|string|unique:users',
            'department' => 'required|string',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'id_number' => $validated['id_number'],
            'department' => $validated['department'],
            'password' => bcrypt($validated['password'])
        ]);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true]);
    }
}