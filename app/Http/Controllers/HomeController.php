<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard()
    {
        $user = auth()->user();
        $receivedDocuments = \App\Models\CommunicationForm::where('to', $user->name)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $documents = $receivedDocuments->map(function ($document) {
            return [
                'id' => $document->id,
                'sender' => $document->from,
                'subject' => $document->file_type,
                'fileType' => $document->file_type,
                'dateReceived' => $document->created_at->format('Y-m-d H:i'),
                'due_date' => $document->due_date,
                'action' => $document->action_items ? implode(', ', $document->action_items) : 'No action required',
                'additionalAction' => $document->additional_actions ? implode(', ', $document->additional_actions) : '',
                'notes' => $document->additional_notes ?? 'No notes',
            ];
        });

        $allDueDates = \App\Models\CommunicationForm::where('to', $user->name)
            ->pluck('due_date')
            ->filter()
            ->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            })
            ->values();

        return view('dashboard', [
            'documents' => $documents,
            'paginator' => $receivedDocuments,
            'allDueDates' => $allDueDates,
        ]);
    }
}
