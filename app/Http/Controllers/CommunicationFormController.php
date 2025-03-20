<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommunicationForm;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CommunicationFormController extends Controller
{
    /**
     * Display the form.
     */
    public function index()
    {
        return view('index');
    }

    /**
     * Handle form submission.
     */
    public function store(Request $request)
    {
        // Log the incoming request data for debugging
        \Log::info('Form submission data:', $request->all());

        try {
            // Validate the form data
            $request->validate([
                'to' => 'required|string|max:255',
                'attention' => 'required|string|max:255',
                'departments' => 'nullable|array',
                'departments.*' => 'nullable|string',
                'action_items' => 'nullable|array',
                'action_items.*' => 'nullable|string',
                'additional_actions' => 'nullable|array',
                'additional_actions.*' => 'nullable|string',
                'file_type' => 'nullable|string',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file',
            ]);

            // Handle file uploads
            $uploadedFiles = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('uploads'); // Save files to the 'uploads' directory
                    $uploadedFiles[] = $path;
                }
            }

            // Get the authenticated user's name as the sender ("from")
            $senderName = Auth::user()->name;

            // Save data to the database
            CommunicationForm::create([
                'to' => $request->input('to'),
                'from' => $senderName,
                'attention' => $request->input('attention'),
                'departments' => $request->input('departments'),
                'action_items' => $request->input('action_items'),
                'additional_actions' => $request->input('additional_actions'),
                'file_type' => $request->input('file_type'),
                'files' => $uploadedFiles,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully!',
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Form submission error:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting the form.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch users based on selected departments and optional search term.
     */
    public function fetchUsers(Request $request)
    {
        $request->validate([
            'departments' => 'required|array',
            'departments.*' => 'string',
            'search' => 'nullable|string',
        ]);

        $users = User::whereIn('department', $request->input('departments'));

        // Filter by search term if provided
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $users->where('name', 'LIKE', "%{$searchTerm}%");
        }

        return response()->json($users->get());
    }

    /**
     * Display received documents for the authenticated user.
     */
    public function receivedDocuments()
    {
        $user = Auth::user();
        $receivedDocuments = CommunicationForm::where('to', $user->name)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('received', compact('receivedDocuments'));
    }
}
