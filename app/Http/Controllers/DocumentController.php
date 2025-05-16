<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|file',
            'due_date' => 'required|date',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('documents');

        Document::create([
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $filePath,
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('documents.index')->with('success', 'Document uploaded successfully.');
    }
} 