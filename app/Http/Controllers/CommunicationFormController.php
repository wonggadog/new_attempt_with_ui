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
                'additional_notes' => $request->input('additional_notes'),
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

        // Transform the data to match the structure expected by the JavaScript
        $documents = $receivedDocuments->map(function ($document) {
            return [
                'id' => $document->id,
                'sender' => $document->from,
                'senderEmail' => '', // Add sender email if available
                'subject' => $document->file_type, // Use file type as subject or customize as needed
                'fileType' => $document->file_type,
                'iconClass' => $this->getFileIconClass($document->file_type), // Helper function to map file type to icon
                'iconColor' => $this->getFileIconColor($document->file_type), // Helper function to map file type to color
                'dateReceived' => $document->created_at->toIso8601String(),
                'action' => $document->action_items ? implode(', ', $document->action_items) : 'No action required',
                'additionalAction' => $document->additional_actions ? implode(', ', $document->additional_actions) : '',
                'notes' => $document->additional_notes ?? 'No notes',
                'isStarred' => false, // Add logic for starred documents if needed
                'isUrgent' => false, // Add logic for urgent documents if needed
            ];
        });

        return view('received', ['documents' => $documents]);
    }

    // Helper function to map file type to icon class
    private function getFileIconClass($fileType)
    {
        switch ($fileType) {
            case 'PDF':
                return 'bi-file-earmark-pdf';
            case 'Image':
                return 'bi-file-earmark-image';
            case 'Document':
                return 'bi-file-earmark-text';
            case 'Presentation':
                return 'bi-file-earmark-slides';
            default:
                return 'bi-file-earmark';
        }
    }

    // Helper function to map file type to icon color
    private function getFileIconColor($fileType)
    {
        switch ($fileType) {
            case 'PDF':
                return 'pdf';
            case 'Image':
                return 'image';
            case 'Document':
                return 'doc';
            case 'Presentation':
                return 'presentation';
            default:
                return 'doc';
        }
    }
}
