<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommunicationFormController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminControlsController;
use App\Http\Controllers\FileTypeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\GoogleDriveController;
use App\Mail\MyEmail;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentReceived;

Auth::routes();

// Google Authentication Routes
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Google Drive Routes
Route::middleware('auth')->group(function () {
    Route::get('/google-drive/connect', [GoogleDriveController::class, 'connect'])->name('google.drive.connect');
    Route::get('/google-drive/callback', [GoogleDriveController::class, 'callback'])->name('google.drive.callback');
    Route::post('/google-drive/disconnect', [GoogleDriveController::class, 'disconnect'])->name('google.drive.disconnect');
    Route::post('/google-drive/upload/{recipient}', [GoogleDriveController::class, 'uploadToRecipient'])->name('google.drive.upload');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', [CommunicationFormController::class, 'index'])->name('home');
    Route::post('/submit-form', [CommunicationFormController::class, 'store'])->name('submit.form');
    Route::post('/fetch-users', [CommunicationFormController::class, 'fetchUsers'])->name('fetch.users');
    Route::get('/received-documents', [CommunicationFormController::class, 'receivedDocuments'])->name('received.documents');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin Controls
    Route::get('/admin_controls', [AdminControlsController::class, 'admin_controls'])->name('admin_controls');
    Route::get('/admin_controls/users', [AdminControlsController::class, 'index']);
    Route::post('/admin_controls/users', [AdminControlsController::class, 'store']);
    Route::delete('/admin_controls/users/{user}', [AdminControlsController::class, 'destroy']);
    Route::put('/admin_controls/users/{user}', [AdminControlsController::class, 'update']);

    // File Type Management Routes
    Route::prefix('admin')->group(function () {
        Route::resource('file-types', FileTypeController::class)->names([
            'index' => 'admin.file-types.index',
            'store' => 'admin.file-types.store',
            'update' => 'admin.file-types.update',
            'destroy' => 'admin.file-types.destroy'
        ]);
        Route::get('/file-types/list', [FileTypeController::class, 'getFileTypes'])->name('file-types.list');
        Route::put('file-types/bulk-update', [FileTypeController::class, 'bulkUpdate'])->name('admin.file-types.bulk-update');
    });

    // File Types for Upload Form
    Route::get('/file-types/options', [FileTypeController::class, 'getFileTypeOptions'])->name('file-types.options');
    
    // Add a route to display logs in the browser
    Route::get('/logs', function () {
        $logFile = storage_path('logs/laravel.log');

        if (file_exists($logFile)) {
            return response()->file($logFile);
        } else {
            return response('Log file not found.', 404);
        }
    })->middleware('auth')->name('view.logs');

    // Add a route for a beginner-friendly log viewer
    Route::get('/log-viewer', function () {
        $logFile = storage_path('logs/laravel.log');

        if (!file_exists($logFile)) {
            return response('Log file not found.', 404);
        }

        $logs = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $formattedLogs = array_map(function ($log) {
            // Attempt to parse the log line into components
            preg_match('/\[(.*?)\] (\w+): (.*)/', $log, $matches);
            return [
                'timestamp' => $matches[1] ?? 'Unknown',
                'level' => $matches[2] ?? 'Unknown',
                'message' => $matches[3] ?? $log,
            ];
        }, $logs);

        return view('log-viewer', ['logs' => $formattedLogs]);
    })->middleware('auth')->name('log.viewer');

    // Route for the dashboard view
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');

    // Route for the sent tracking view
    Route::get('/sent-tracking', [App\Http\Controllers\CommunicationFormController::class, 'sentDocuments'])->name('sent.tracking');

    // AJAX route for fetching a single document's timeline
    Route::get('/sent-tracking/timeline/{id}', [App\Http\Controllers\CommunicationFormController::class, 'sentDocumentTimeline'])->name('sent.tracking.timeline');

    Route::get('/download/{form}', [CommunicationFormController::class, 'download'])->name('communication-form.download');

    Route::post('/forward/{id}', [CommunicationFormController::class, 'forward'])->name('forward');
    Route::post('/send-back/{id}', [CommunicationFormController::class, 'sendBack'])->name('send-back');

    // Route for the trash page
    Route::get('/trash', function () {
        return view('trash');
    })->name('trash');

    // Trash API routes
    Route::get('/api/trash', [App\Http\Controllers\CommunicationFormController::class, 'trashedDocuments'])->name('api.trash.list');
    Route::post('/api/trash/{id}/delete', [App\Http\Controllers\CommunicationFormController::class, 'moveToTrash'])->name('api.trash.move');
    Route::post('/api/trash/{id}/restore', [App\Http\Controllers\CommunicationFormController::class, 'restoreFromTrash'])->name('api.trash.restore');
    Route::delete('/api/trash/{id}/force', [App\Http\Controllers\CommunicationFormController::class, 'forceDeleteFromTrash'])->name('api.trash.forceDelete');
    Route::post('/api/trash/restore-all', [App\Http\Controllers\CommunicationFormController::class, 'restoreAllFromTrash'])->name('api.trash.restoreAll');
    Route::delete('/api/trash/empty', [App\Http\Controllers\CommunicationFormController::class, 'emptyTrash'])->name('api.trash.empty');
});

// Test route for CSS file
Route::get('/test-css', function () {
    return response()->file(public_path('css/file-types.css'));
});

Route::get('/test-email', function () {
    // Get some real users from the database
    $recipients = \App\Models\User::take(3)->get();
    $sender = \App\Models\User::first();
    
    foreach ($recipients as $recipient) {
        Mail::to($recipient->email)->send(new DocumentReceived(
            $recipient->name,
            $sender->name,
            'Test Document Upload',
            'test_document.pdf',
            'For appropriate action, For compliance',
            'Please review and sign',
            'This is a test notification using the actual document notification system.'
        ));
    }
    
    return 'Test emails sent to: ' . $recipients->pluck('email')->implode(', ');
});
