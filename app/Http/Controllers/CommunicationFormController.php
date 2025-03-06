<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommunicationForm;
use Illuminate\Support\Facades\Storage;
use App\Models\Recipient;

class CommunicationFormController extends Controller
{
    // Display the form
    public function index()
    {
        return view('index');
    }

    // Handle form submission
    public function store(Request $request)
    {
        // Log the incoming request data for debugging
        \Log::info('Form submission data:', $request->all());

        try {
            // Validate the form data
            $request->validate([
                'to' => 'required|string|max:255',
                'attention' => 'required|string|max:255',
                'departments' => 'nullable|array', // Expects an array
                'departments.*' => 'nullable|string', // Each item in the array should be a string
                'action_items' => 'nullable|array', // Expects an array
                'action_items.*' => 'nullable|string', // Each item in the array should be a string
                'additional_actions' => 'nullable|array', // Expects an array
                'additional_actions.*' => 'nullable|string', // Each item in the array should be a string
                'file_type' => 'nullable|string',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file', // Each item in the array should be a file
            ]);

            // Handle file uploads (if applicable)
            $uploadedFiles = [];
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('uploads'); // Save files to storage
                    $uploadedFiles[] = $path;
                }
            }

            // Save data to the database
            CommunicationForm::create([
                'to' => $request->input('to'),
                'attention' => $request->input('attention'),
                'departments' => $request->input('departments'),
                'action_items' => $request->input('action_items'),
                'additional_actions' => $request->input('additional_actions'),
                'file_type' => $request->input('file_type'),
                'files' => $uploadedFiles,
            ]);

            // Return a JSON response
            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully!',
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Form submission error:', ['error' => $e->getMessage()]);

            // Return a JSON error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting the form.',
                'error' => $e->getMessage(), // Include the error message in the response
            ], 500);
        }
    }

    public function fetchRecipients(Request $request)
    {
        // Validate the request
        $request->validate([
            'departments' => 'required|array',
            'departments.*' => 'string',
            'search' => 'nullable|string', // Add search term validation
        ]);

        // Fetch recipients based on the selected departments
        $recipients = Recipient::whereIn('department', $request->input('departments'));

        // If a search term is provided, filter recipients by name
        if ($request->has('search') && !empty($request->input('search'))) {
            $searchTerm = $request->input('search');
            $recipients->where('name', 'LIKE', "%{$searchTerm}%");
        }

        // Return the recipients as JSON
        return response()->json($recipients->get());
    }
}