<?php

namespace App\Http\Controllers;

use App\Models\FileType;
use Illuminate\Http\Request;

class FileTypeController extends Controller
{
    public function index()
    {
        $fileTypes = FileType::all();
        return view('admin.file-types.index', compact('fileTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:file_types',
        ]);

        $fileType = FileType::create($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'fileType' => $fileType
            ]);
        }

        return redirect()->route('admin.file-types.index')
            ->with('success', 'File type created successfully.');
    }

    public function update(Request $request, FileType $fileType)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:file_types,name,' . $fileType->id,
        ]);

        $fileType->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'name' => $fileType->name
            ]);
        }

        return redirect()->route('admin.file-types.index')
            ->with('success', 'File type updated successfully.');
    }

    public function destroy(FileType $fileType)
    {
        $fileType->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true
            ]);
        }

        return redirect()->route('admin.file-types.index')
            ->with('success', 'File type deleted successfully.');
    }

    public function getFileTypes()
    {
        $fileTypes = FileType::orderBy('name')->pluck('name');
        return response()->json($fileTypes);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'file_types' => 'required|array',
            'file_types.*.name' => 'required|string|max:255|unique:file_types,name',
        ]);

        foreach ($request->file_types as $id => $data) {
            $fileType = FileType::findOrFail($id);
            $fileType->update($data);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'File types updated successfully.'
            ]);
        }

        return redirect()->route('admin.file-types.index')
            ->with('success', 'File types updated successfully.');
    }

    public function getFileTypeOptions()
    {
        $fileTypes = FileType::orderBy('name')->get();
        return response()->json($fileTypes);
    }
} 