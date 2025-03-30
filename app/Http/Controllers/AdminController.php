<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.controls', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'idNumber' => 'required|string|unique:users,id_number',
            'department' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'id_number' => $validated['idNumber'],
            'department' => $validated['department'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['success' => 'User created successfully!']);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => 'User deleted successfully!']);
    }
}