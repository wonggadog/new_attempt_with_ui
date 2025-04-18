<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminControlsController extends Controller
{
    public function admin_controls()
    {
        return view('admin_controls');
    }

    public function index(Request $request)
    {
        $query = User::query()->orderBy('created_at', 'desc');
        
        // Apply department filter if provided and not 'all'
        if ($request->has('department') && $request->department !== 'all') {
            $query->where('department', $request->department);
        }
        
        $users = $query->paginate(10);
        
        return response()->json([
            'success' => true,
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total()
            ],
            'filters' => [
                'department' => $request->department ?? 'all'
            ]
        ]);
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

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'id_number' => 'required|string|unique:users,id_number,' . $user->id,
            'department' => 'required|string'
        ]);

        $user->update($validated);

        return response()->json(['success' => true, 'user' => $user]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true]);
    }
}