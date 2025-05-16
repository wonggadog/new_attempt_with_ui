<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CommunicationForm;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\GoogleDriveService;
use App\Mail\DocumentReceived;
use Illuminate\Support\Facades\Mail;

class CommunicationFormController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

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
        $request->validate([
            'to' => 'required|array', // Expect an array of recipients
            'to.*' => 'string|max:255',
            'attention' => 'required|string|max:255',
            'departments' => 'nullable|array',
            'departments.*' => 'nullable|string',
            'action_items' => 'nullable|array',
            'action_items.*' => 'nullable|string',
            'additional_actions' => 'nullable|array',
            'additional_actions.*' => 'nullable|string',
            'file_type' => 'nullable|string|max:255',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240', // Max file size 10MB
            'additional_notes' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $uploadedFiles = [];
        $googleDriveFileIds = [];

        foreach ($request->input('to') as $recipientName) {
            $recipient = User::where('name', $recipientName)->first();

            if ($recipient) {
                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $path = $file->store('uploads');
                        $uploadedFiles[] = [
                            'path' => $path,
                            'original' => $file->getClientOriginalName(),
                        ];

                        if ($recipient->google_drive_connected) {
                            try {
                                $this->driveService->setAccessToken(json_decode($recipient->google_drive_token, true));
                                $fileId = $this->driveService->uploadFile(
                                    $file->getPathname(),
                                    $file->getClientOriginalName(),
                                    $recipient->google_drive_folder_id
                                );
                                $googleDriveFileIds[] = $fileId;
                            } catch (\Exception $e) {
                                \Log::error('Google Drive upload failed: ' . $e->getMessage());
                            }
                        }
                    }
                }

                try {
                    Mail::to($recipient->email)->send(new DocumentReceived(
                        $recipientName,
                        Auth::user()->name,
                        $request->input('attention'),
                        $uploadedFiles[0]['path'] ?? 'No file',
                        implode(', ', $request->input('action_items', [])),
                        implode(', ', $request->input('additional_actions', [])),
                        $request->input('additional_notes', 'No notes')
                    ));
                } catch (\Exception $e) {
                    \Log::error('Mail send failed: ' . $e->getMessage());
                }

                CommunicationForm::create([
                    'to' => $recipientName,
                    'from' => Auth::user()->name,
                    'attention' => $request->input('attention'),
                    'departments' => $request->input('departments'),
                    'action_items' => $request->input('action_items'),
                    'additional_actions' => $request->input('additional_actions'),
                    'file_type' => $request->input('file_type'),
                    'files' => $uploadedFiles,
                    'google_drive_file_ids' => $googleDriveFileIds,
                    'additional_notes' => $request->input('additional_notes'),
                    'due_date' => $request->input('due_date'),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Form submitted successfully!']);
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
            $files = collect($document->files)->map(function ($file) {
                if (is_array($file) && isset($file['path'], $file['original'])) {
                    return $file;
                } elseif (is_string($file)) {
                    // Backward compatibility: if only path is stored
                    return ['path' => $file, 'original' => basename($file)];
                }
                return null;
            })->filter()->values()->toArray();
            return [
                'id' => $document->id,
                'sender' => $document->from,
                'senderEmail' => '',
                'subject' => $document->file_type,
                'fileType' => $document->file_type,
                'iconClass' => $this->getFileIconClass($document->file_type),
                'iconColor' => $this->getFileIconColor($document->file_type),
                'dateReceived' => $document->created_at->toIso8601String(),
                'action' => $document->action_items ? implode(', ', $document->action_items) : 'No action required',
                'additionalAction' => $document->additional_actions ? implode(', ', $document->additional_actions) : '',
                'notes' => $document->additional_notes ?? 'No notes',
                'isStarred' => false,
                'isUrgent' => false,
                'files' => $files,
            ];
        });

        return view('received', ['documents' => $documents]);
    }

    /**
     * Display sent documents for the authenticated user.
     */
    public function sentDocuments()
    {
        $user = Auth::user();
        $sentDocuments = CommunicationForm::where('from', $user->name)
            ->orderBy('created_at', 'desc')
            ->with('statuses')
            ->paginate(10);

        return view('sent_tracking', ['documents' => $sentDocuments]);
    }

    /**
     * Return the timeline for a single sent document (AJAX).
     */
    public function sentDocumentTimeline($id)
    {
        $doc = CommunicationForm::with('statuses')->findOrFail($id);
        return view('partials.sent_timeline', compact('doc'))->render();
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

    /**
     * Download the uploaded file for a CommunicationForm (recipient only).
     */
    public function download($formId)
    {
        $form = CommunicationForm::findOrFail($formId);
        $user = Auth::user();
        // Only the recipient can download
        if ($form->to !== $user->name) {
            abort(403, 'You are not authorized to download this file.');
        }
        $files = $form->files;
        if (!$files || !is_array($files) || !isset($files[0]['path'])) {
            abort(404, 'File not found.');
        }
        $filePath = $files[0]['path'];
        $originalName = $files[0]['original'] ?? basename($filePath);
        if (!\Storage::exists($filePath)) {
            abort(404, 'File not found on server.');
        }
        return \Storage::download($filePath, $originalName);
    }

    /**
     * Forward a document to another user (recipient only).
     */
    public function forward(Request $request, $formId)
    {
        $form = CommunicationForm::findOrFail($formId);
        $user = Auth::user();
        // Only the recipient can forward
        if ($form->to !== $user->name) {
            return response()->json(['success' => false, 'message' => 'You are not authorized to forward this document.'], 403);
        }
        $request->validate([
            'recipient' => 'required|string|exists:users,name',
            'note' => 'nullable|string',
        ]);
        $recipientName = $request->input('recipient');
        $note = $request->input('note');
        // Duplicate the form with new recipient and note
        $newForm = CommunicationForm::create([
            'to' => $recipientName,
            'from' => $user->name,
            'attention' => $form->attention,
            'departments' => $form->departments,
            'action_items' => $form->action_items,
            'additional_actions' => $form->additional_actions,
            'file_type' => $form->file_type,
            'files' => $form->files,
            'google_drive_file_ids' => $form->google_drive_file_ids,
            'additional_notes' => $note ?? $form->additional_notes,
        ]);
        return response()->json(['success' => true, 'message' => 'Document forwarded successfully!']);
    }

    /**
     * List trashed documents for the authenticated user (sent or received).
     */
    public function trashedDocuments()
    {
        $user = Auth::user();
        $trashed = CommunicationForm::onlyTrashed()
            ->where(function ($query) use ($user) {
                $query->where('to', $user->name)
                      ->orWhere('from', $user->name);
            })
            ->orderBy('deleted_at', 'desc')
            ->get();

        $documents = $trashed->map(function ($document) {
            $files = collect($document->files)->map(function ($file) {
                if (is_array($file) && isset($file['path'], $file['original'])) {
                    return $file;
                } elseif (is_string($file)) {
                    return ['path' => $file, 'original' => basename($file)];
                }
                return null;
            })->filter()->values()->toArray();
            return [
                'id' => $document->id,
                'sender' => $document->from,
                'recipient' => $document->to,
                'subject' => $document->file_type,
                'fileType' => $document->file_type,
                'dateDeleted' => $document->deleted_at->toIso8601String(),
                'files' => $files,
                'notes' => $document->additional_notes ?? 'No notes',
            ];
        });
        return response()->json(['documents' => $documents]);
    }

    /**
     * Soft-delete (move to trash) a document.
     */
    public function moveToTrash($id)
    {
        $user = Auth::user();
        $doc = CommunicationForm::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('to', $user->name)
                      ->orWhere('from', $user->name);
            })->firstOrFail();
        $doc->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Restore a document from trash.
     */
    public function restoreFromTrash($id)
    {
        $user = Auth::user();
        $doc = CommunicationForm::onlyTrashed()
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('to', $user->name)
                      ->orWhere('from', $user->name);
            })->firstOrFail();
        $doc->restore();
        return response()->json(['success' => true]);
    }

    /**
     * Permanently delete a document from trash.
     */
    public function forceDeleteFromTrash($id)
    {
        $user = Auth::user();
        $doc = CommunicationForm::onlyTrashed()
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('to', $user->name)
                      ->orWhere('from', $user->name);
            })->firstOrFail();
        $doc->forceDelete();
        return response()->json(['success' => true]);
    }

    /**
     * Restore all trashed documents for the user.
     */
    public function restoreAllFromTrash()
    {
        $user = Auth::user();
        $docs = CommunicationForm::onlyTrashed()
            ->where(function ($query) use ($user) {
                $query->where('to', $user->name)
                      ->orWhere('from', $user->name);
            })->get();
        foreach ($docs as $doc) {
            $doc->restore();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Permanently delete all trashed documents for the user.
     */
    public function emptyTrash()
    {
        $user = Auth::user();
        $docs = CommunicationForm::onlyTrashed()
            ->where(function ($query) use ($user) {
                $query->where('to', $user->name)
                      ->orWhere('from', $user->name);
            })->get();
        foreach ($docs as $doc) {
            $doc->forceDelete();
        }
        return response()->json(['success' => true]);
    }

    /**
     * Handle sending back a document to the original sender.
     */
    public function sendBack(Request $request, $id)
    {
        try {
            $document = CommunicationForm::findOrFail($id);
            $originalSender = User::where('name', $document->from)->first();
            
            if (!$originalSender) {
                return response()->json(['success' => false, 'message' => 'Original sender not found.'], 404);
            }

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('uploads');
                $uploadedFile = [
                    'path' => $path,
                    'original' => $file->getClientOriginalName(),
                ];

                // Upload to Google Drive if sender is connected
                $googleDriveFileId = null;
                if ($originalSender->google_drive_connected) {
                    try {
                        $this->driveService->setAccessToken(json_decode($originalSender->google_drive_token, true));
                        $googleDriveFileId = $this->driveService->uploadFile(
                            $file->getPathname(),
                            $file->getClientOriginalName(),
                            $originalSender->google_drive_folder_id
                        );
                    } catch (\Exception $e) {
                        \Log::error('Google Drive upload failed: ' . $e->getMessage());
                    }
                }

                // Create new document for the sender
                CommunicationForm::create([
                    'to' => $originalSender->name,
                    'from' => Auth::user()->name,
                    'attention' => $document->attention,
                    'departments' => $document->departments,
                    'action_items' => $document->action_items,
                    'additional_actions' => $document->additional_actions,
                    'file_type' => '[Sent Back] ' . $document->file_type,
                    'files' => [$uploadedFile],
                    'google_drive_file_ids' => $googleDriveFileId ? [$googleDriveFileId] : [],
                    'additional_notes' => $request->input('note', 'Document sent back with modifications.'),
                ]);

                // Send email notification
                try {
                    Mail::to($originalSender->email)->send(new DocumentReceived(
                        $originalSender->name,
                        Auth::user()->name,
                        $document->attention,
                        $uploadedFile['path'],
                        implode(', ', $document->action_items),
                        implode(', ', $document->additional_actions),
                        $request->input('note', 'Document sent back with modifications.')
                    ));
                } catch (\Exception $e) {
                    \Log::error('Mail send failed: ' . $e->getMessage());
                }

                return response()->json(['success' => true, 'message' => 'Document sent back successfully!']);
            }

            return response()->json(['success' => false, 'message' => 'No file uploaded.'], 400);
        } catch (\Exception $e) {
            \Log::error('Send back failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send back document.'], 500);
        }
    }
}
