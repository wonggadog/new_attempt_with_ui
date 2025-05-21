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

Route::get('/test-middleware', function() {
    return \App\Http\Middleware\CheckAdmin::class;
});

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

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::post('/received-documents/mark-complete/{id}', [App\Http\Controllers\CommunicationFormController::class, 'markAsComplete'])->name('received.markComplete');
    Route::post('/received-documents/{id}/comment', [CommunicationFormController::class, 'sendComment'])->name('received.comment');

    Route::post('/api/documents/{id}/mark-as-read', [CommunicationFormController::class, 'markAsRead'])->name('documents.markAsRead');
    Route::post('/api/documents/{id}/mark-as-acknowledged', [CommunicationFormController::class, 'markAsAcknowledged'])->name('documents.markAsAcknowledged');
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

Route::get('/settings', function () {
    return view('settings');
})->name('settings');


Route::get('/love-letter', function () {
    return '
        <html>
            <head>
                <meta charset="UTF-8">
                <title>With all love, Dian</title>
                <style>
                    body {
                        font-family: "Georgia", serif;
                        background: linear-gradient(to bottom, #fff0f5, #fce4ec);
                        color: #3a2d3d;
                        padding: 2.5rem;
                        max-width: 700px;
                        margin: auto;
                        line-height: 1.9;
                        text-align: justify;
                    }

                    h1 {
                        text-align: center;
                        font-style: italic;
                        color: #d63384;
                        margin-bottom: 1.5rem;
                        font-size: 2rem;
                    }

                    p {
                        margin-bottom: 1.5rem;
                        text-indent: 1em;
                    }

                    strong {
                        color: #b30059;
                        font-weight: bold;
                    }

                    blockquote {
                        border-left: 4px solid #d63384;
                        padding-left: 1rem;
                        margin: 1.5rem 0;
                        font-style: italic;
                        color: #555;
                    }

                    footer {
                        text-align: right;
                        margin-top: 2rem;
                        font-style: italic;
                    }

                    hr {
                        border: 0;
                        height: 1px;
                        background: linear-gradient(to right, transparent, #d63384, transparent);
                        margin: 3rem 0;
                    }
                </style>
            </head>
            <body>
                <h1>A Letter From My Heart</h1>

                <p>Hi, I know – weird way to tell you something. But hey, this is the website I made for my OJT. I don’t know until when this will be here but, if you’re reading this – I want you to know that I love you. From the day I asked you if I could court you, until now. And I’d always be willing to be there for you, always. From start to finish, day in or day out. No matter what.</p>

                <p>I love the way you smile, the way you laugh, the way you talk, walk and how your eyes twinkle when you talk about something you love. Or the frustration in your voice when you talk to me about something that happened with your day. I love everything about you. Always have, always will. I’m not willing to let us go, I’ll be willing to make us right. Argue if we must, but I’d always choose to make it right by you.</p>

                <p>Because no other person has ever made me feel like I actually mattered. Or that there’s something in me that is still worth loving. If anything, I don’t ever want you to go. I know I sound selfish, and I know that I sound like a million other guys saying how much they want their girl to stay, but trust me – with my actions and my words that I show you – this is my vulnerable, honest and genuine self.</p>

                <p><strong>So please.</strong></p>

                <hr>

                <p>Your time, I respect. Your dreams, I wish for you. Your safety, is what matters to me. And your heart, pure and gold, the one thing I wish to take care of.</p>

                <p>With you I am strong, with you I feel like I can be myself. For you, I would move a mountain or die trying. I’d walk through hell and come back if you ask me to.</p>

                <blockquote>
                    “So it&#39s not gonna be easy. It&#39s going to be really hard; we&#39re gonna have to work at this everyday, but I want to do that because I want you. I want all of you, forever, everyday.”
                    <br><em>– Noah, The Notebook</em>
                </blockquote>

                <p>And no other words I say would ever explain better what I feel. But what I feel weighs so much more than the romance in The Notebook.</p>

                <p><strong>So please – don’t go.</strong></p>

                <p>In all ways, through all of time, always.</p>

                <p>I love you, beb.</p>

                <footer>
                    Through thick and thin,<br>Dian.
                </footer>
            </body>
        </html>
    ';
});