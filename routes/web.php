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


Route::get('/ttat', function () {
    return '
        <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Through thick and thin</title>
                <script src="https://cdn.tailwindcss.com"></script>
                <style>
                    /* Custom animations and effects */
                    body {
                        font-family: "Georgia", serif;
                        margin: 0;
                        padding: 0;
                        min-height: 100vh;
                        background: linear-gradient(-45deg, #ffecf2, #ffe6eb, #ffe0f0, #fde5ff);
                        background-size: 400% 400%;
                        animation: gradientBG 15s ease infinite;
                        color: #3a2d3d;
                        line-height: 1.9;
                        overflow-x: hidden;
                        position: relative;
                        perspective: 1000px; /* Enhanced 3D perspective for parallax */
                    }

                    @keyframes gradientBG {
                        0% { background-position: 0% 50%; }
                        50% { background-position: 100% 50%; }
                        100% { background-position: 0% 50%; }
                    }

                    /* ENHANCED Parallax layers with 3D transforms */
                    .parallax-container {
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        pointer-events: none;
                        z-index: -1;
                        perspective: 1000px;
                        transform-style: preserve-3d;
                        overflow: hidden;
                    }

                    .parallax-layer {
                        position: absolute;
                        top: -20%;
                        left: -20%;
                        right: -20%;
                        bottom: -20%;
                        will-change: transform;
                        transform-style: preserve-3d;
                    }

                    .parallax-layer-1 {
                        background: radial-gradient(circle at 20% 20%, rgba(255, 182, 193, 0.5) 0%, transparent 40%);
                        transform: translateZ(-200px) scale(1.5);
                    }

                    .parallax-layer-2 {
                        background: radial-gradient(circle at 80% 40%, rgba(255, 192, 203, 0.5) 0%, transparent 40%);
                        transform: translateZ(-400px) scale(2);
                    }

                    .parallax-layer-3 {
                        background: radial-gradient(circle at 40% 70%, rgba(255, 228, 225, 0.4) 0%, transparent 40%);
                        transform: translateZ(-600px) scale(2.5);
                    }

                    /* NEW: Floating clouds for enhanced parallax */
                    .cloud {
                        position: absolute;
                        background: rgba(255, 255, 255, 0.6);
                        border-radius: 50%;
                        filter: blur(20px);
                        pointer-events: none;
                    }

                    /* NEW: Star sparkles with glow effect */
                    .star {
                        position: absolute;
                        pointer-events: none;
                        z-index: 5;
                        animation: twinkle 4s ease-in-out infinite;
                    }

                    @keyframes twinkle {
                        0%, 100% { opacity: 0.3; transform: scale(0.8); }
                        50% { opacity: 1; transform: scale(1.2); }
                    }

                    /* ENHANCED Floating sparkles with different sizes and colors */
                    .sparkle {
                        position: absolute;
                        border-radius: 50%;
                        pointer-events: none;
                        z-index: 10;
                    }

                    .sparkle-gold {
                        background: linear-gradient(45deg, #ffd700, #ffed4e);
                        box-shadow: 0 0 10px #ffd700, 0 0 20px rgba(255, 215, 0, 0.5);
                    }

                    .sparkle-pink {
                        background: linear-gradient(45deg, #ff69b4, #ff9ed2);
                        box-shadow: 0 0 10px #ff69b4, 0 0 20px rgba(255, 105, 180, 0.5);
                    }

                    .sparkle-purple {
                        background: linear-gradient(45deg, #da70d6, #e6a8d7);
                        box-shadow: 0 0 10px #da70d6, 0 0 20px rgba(218, 112, 214, 0.5);
                    }

                    .sparkle:before {
                        content: "";
                        position: absolute;
                        top: -100%;
                        left: -100%;
                        right: -100%;
                        bottom: -100%;
                        border-radius: 50%;
                        z-index: -1;
                        opacity: 0.5;
                    }

                    .sparkle-gold:before {
                        background: radial-gradient(circle, rgba(255, 215, 0, 0.8), transparent 70%);
                    }

                    .sparkle-pink:before {
                        background: radial-gradient(circle, rgba(255, 105, 180, 0.8), transparent 70%);
                    }

                    .sparkle-purple:before {
                        background: radial-gradient(circle, rgba(218, 112, 214, 0.8), transparent 70%);
                    }

                    .sparkle-tiny {
                        width: 3px;
                        height: 3px;
                    }

                    .sparkle-small {
                        width: 5px;
                        height: 5px;
                    }

                    .sparkle-medium {
                        width: 8px;
                        height: 8px;
                    }

                    .sparkle-large {
                        width: 12px;
                        height: 12px;
                    }

                    /* NEW: Different sparkle animations */
                    .sparkle-float-1 {
                        animation: sparkleFloat1 8s ease-in-out infinite;
                    }

                    .sparkle-float-2 {
                        animation: sparkleFloat2 10s ease-in-out infinite;
                    }

                    .sparkle-float-3 {
                        animation: sparkleFloat3 12s ease-in-out infinite;
                    }

                    .sparkle-pulse {
                        animation: sparklePulse 4s ease-in-out infinite;
                    }

                    @keyframes sparkleFloat1 {
                        0%, 100% { 
                            transform: translateY(0px) translateX(0px) scale(1) rotate(0deg);
                            opacity: 0.7;
                        }
                        25% { 
                            transform: translateY(-50px) translateX(25px) scale(1.2) rotate(90deg);
                            opacity: 1;
                        }
                        50% { 
                            transform: translateY(-20px) translateX(-15px) scale(0.8) rotate(180deg);
                            opacity: 0.8;
                        }
                        75% { 
                            transform: translateY(-70px) translateX(35px) scale(1.1) rotate(270deg);
                            opacity: 0.9;
                        }
                    }

                    @keyframes sparkleFloat2 {
                        0%, 100% { 
                            transform: translateY(0px) translateX(0px) scale(1) rotate(0deg);
                            opacity: 0.6;
                        }
                        33% { 
                            transform: translateY(-30px) translateX(-40px) scale(1.3) rotate(120deg);
                            opacity: 1;
                        }
                        66% { 
                            transform: translateY(-60px) translateX(20px) scale(0.9) rotate(240deg);
                            opacity: 0.8;
                        }
                    }

                    @keyframes sparkleFloat3 {
                        0%, 100% { 
                            transform: translateY(0px) translateX(0px) scale(1) rotate(0deg);
                            opacity: 0.5;
                        }
                        50% { 
                            transform: translateY(-100px) translateX(10px) scale(1.4) rotate(180deg);
                            opacity: 1;
                        }
                    }

                    @keyframes sparklePulse {
                        0%, 100% { 
                            transform: scale(0.8);
                            opacity: 0.5;
                        }
                        50% { 
                            transform: scale(1.5);
                            opacity: 1;
                        }
                    }

                    /* ENHANCED Floating hearts with varied animations */
                    .heart {
                        position: absolute;
                        font-size: 24px;
                        pointer-events: none;
                        z-index: 10;
                        text-shadow: 0 0 10px rgba(255, 105, 180, 0.5);
                    }

                    .heart-small {
                        font-size: 16px;
                    }

                    .heart-medium {
                        font-size: 24px;
                    }

                    .heart-large {
                        font-size: 32px;
                    }

                    /* NEW: Different heart animations */
                    .heart-float-up {
                        animation: heartFloatUp 15s linear infinite;
                    }

                    .heart-float-side {
                        animation: heartFloatSide 20s linear infinite;
                    }

                    .heart-pulse {
                        animation: heartPulse 3s ease-in-out infinite;
                    }

                    @keyframes heartFloatUp {
                        0% {
                            transform: translateY(100vh) translateX(0) rotate(0deg);
                            opacity: 0;
                        }
                        10% {
                            opacity: 1;
                        }
                        90% {
                            opacity: 1;
                        }
                        100% {
                            transform: translateY(-100px) translateX(100px) rotate(360deg);
                            opacity: 0;
                        }
                    }

                    @keyframes heartFloatSide {
                        0% {
                            transform: translateX(-100px) translateY(0) rotate(0deg);
                            opacity: 0;
                        }
                        10% {
                            opacity: 1;
                        }
                        90% {
                            opacity: 1;
                        }
                        100% {
                            transform: translateX(calc(100vw + 100px)) translateY(-50px) rotate(360deg);
                            opacity: 0;
                        }
                    }

                    @keyframes heartPulse {
                        0%, 100% {
                            transform: scale(1);
                            opacity: 0.7;
                        }
                        50% {
                            transform: scale(1.5);
                            opacity: 1;
                        }
                    }

                    /* NEW: Glowing border effect */
                    .glow-border {
                        position: relative;
                    }

                    .glow-border::after {
                        content: "";
                        position: absolute;
                        top: -3px;
                        left: -3px;
                        right: -3px;
                        bottom: -3px;
                        border-radius: inherit;
                        background: linear-gradient(45deg, #ff69b4, #da70d6, #ff69b4);
                        z-index: -1;
                        filter: blur(8px);
                        opacity: 0;
                        transition: opacity 0.5s ease;
                    }

                    .glow-border:hover::after {
                        opacity: 1;
                    }

                    /* Content with enhanced parallax */
                    .content-wrapper {
                        position: relative;
                        z-index: 10;
                        transform-style: preserve-3d;
                    }

                    .parallax-content {
                        transform: translateZ(0);
                        will-change: transform;
                        transition: transform 0.1s ease-out;
                    }

                    /* Enhanced text animations */
                    .fade-in-up {
                        opacity: 0;
                        transform: translateY(30px) translateZ(0);
                        animation: fadeInUp 1.2s ease forwards;
                    }

                    @keyframes fadeInUp {
                        to {
                            opacity: 1;
                            transform: translateY(0) translateZ(0);
                        }
                    }

                    /* NEW: Floating animation for content */
                    .float-animation {
                        animation: floatingContent 6s ease-in-out infinite;
                    }

                    @keyframes floatingContent {
                        0%, 100% { transform: translateY(0px); }
                        50% { transform: translateY(-15px); }
                    }

                    /* ENHANCED Glass morphism effect */
                    .glass {
                        background: rgba(255, 255, 255, 0.2);
                        backdrop-filter: blur(12px);
                        border: 1px solid rgba(255, 255, 255, 0.4);
                        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                    }

                    /* NEW: Shimmering glass effect */
                    .glass-shimmer {
                        position: relative;
                        overflow: hidden;
                    }

                    .glass-shimmer::before {
                        content: "";
                        position: absolute;
                        top: -50%;
                        left: -50%;
                        width: 200%;
                        height: 200%;
                        background: linear-gradient(
                            to bottom right,
                            rgba(255, 255, 255, 0) 0%,
                            rgba(255, 255, 255, 0.1) 50%,
                            rgba(255, 255, 255, 0) 100%
                        );
                        transform: rotate(30deg);
                        animation: shimmer 6s linear infinite;
                    }

                    @keyframes shimmer {
                        0% { transform: translateX(-100%) rotate(30deg); }
                        100% { transform: translateX(100%) rotate(30deg); }
                    }

                    /* Enhanced button styles */
                    .btn-romantic {
                        background: linear-gradient(135deg, #ff8fab, #ff6b9d);
                        box-shadow: 0 8px 25px rgba(255, 107, 157, 0.3);
                        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        position: relative;
                        overflow: hidden;
                    }

                    .btn-romantic:before {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: -100%;
                        width: 100%;
                        height: 100%;
                        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
                        transition: left 0.5s;
                    }

                    .btn-romantic:hover:before {
                        left: 100%;
                    }

                    .btn-romantic:hover {
                        transform: translateY(-5px) scale(1.05);
                        box-shadow: 0 15px 35px rgba(255, 107, 157, 0.4);
                    }

                    /* NEW: Pulsing button effect */
                    .btn-pulse {
                        animation: buttonPulse 2s infinite;
                    }

                    @keyframes buttonPulse {
                        0% {
                            box-shadow: 0 0 0 0 rgba(255, 107, 157, 0.7);
                        }
                        70% {
                            box-shadow: 0 0 0 15px rgba(255, 107, 157, 0);
                        }
                        100% {
                            box-shadow: 0 0 0 0 rgba(255, 107, 157, 0);
                        }
                    }

                    /* Responsive adjustments */
                    @media (max-width: 768px) {
                        .heart-large {
                            font-size: 24px;
                        }
                        .heart-medium {
                            font-size: 18px;
                        }
                        .sparkle-large {
                            width: 8px;
                            height: 8px;
                        }
                    }
                </style>
            </head>
            <body class="relative">
                <!-- ENHANCED Parallax Background Layers -->
                <div class="parallax-container" id="parallax-container">
                    <div class="parallax-layer parallax-layer-1" id="layer1"></div>
                    <div class="parallax-layer parallax-layer-2" id="layer2"></div>
                    <div class="parallax-layer parallax-layer-3" id="layer3"></div>
                    
                    <!-- NEW: Floating clouds for enhanced depth -->
                    <div class="cloud" style="width: 300px; height: 300px; top: 10%; left: 5%; opacity: 0.6;" id="cloud1"></div>
                    <div class="cloud" style="width: 200px; height: 200px; top: 30%; right: 10%; opacity: 0.4;" id="cloud2"></div>
                    <div class="cloud" style="width: 400px; height: 250px; bottom: 20%; left: 15%; opacity: 0.5;" id="cloud3"></div>
                    <div class="cloud" style="width: 350px; height: 350px; bottom: 10%; right: 20%; opacity: 0.3;" id="cloud4"></div>
                </div>
                
                <!-- NEW: Star sparkles -->
                <div class="star" style="top: 15%; left: 10%; animation-delay: 0s;">✨</div>
                <div class="star" style="top: 25%; right: 15%; animation-delay: 1s;">✨</div>
                <div class="star" style="top: 60%; left: 5%; animation-delay: 2s;">✨</div>
                <div class="star" style="top: 75%; right: 10%; animation-delay: 1.5s;">✨</div>
                <div class="star" style="top: 40%; left: 20%; animation-delay: 0.5s;">✨</div>
                <div class="star" style="top: 85%; left: 30%; animation-delay: 2.5s;">✨</div>
                <div class="star" style="top: 10%; right: 25%; animation-delay: 1.2s;">✨</div>
                <div class="star" style="top: 50%; right: 30%; animation-delay: 0.8s;">✨</div>
                
                <!-- ENHANCED Floating Sparkles with different colors and animations -->
                <div class="sparkle sparkle-gold sparkle-medium sparkle-float-1" style="top: 10%; left: 5%; animation-delay: 0s;"></div>
                <div class="sparkle sparkle-pink sparkle-large sparkle-float-2" style="top: 20%; right: 8%; animation-delay: 1s;"></div>
                <div class="sparkle sparkle-purple sparkle-small sparkle-float-3" style="top: 35%; left: 3%; animation-delay: 2s;"></div>
                <div class="sparkle sparkle-gold sparkle-medium sparkle-pulse" style="top: 50%; right: 5%; animation-delay: 3s;"></div>
                <div class="sparkle sparkle-pink sparkle-tiny sparkle-float-1" style="top: 65%; left: 7%; animation-delay: 4s;"></div>
                <div class="sparkle sparkle-purple sparkle-large sparkle-float-2" style="top: 80%; right: 10%; animation-delay: 5s;"></div>
                <div class="sparkle sparkle-gold sparkle-medium sparkle-float-3" style="top: 15%; left: 15%; animation-delay: 1.5s;"></div>
                <div class="sparkle sparkle-pink sparkle-small sparkle-pulse" style="top: 45%; right: 15%; animation-delay: 2.5s;"></div>
                <div class="sparkle sparkle-purple sparkle-tiny sparkle-float-1" style="top: 75%; left: 12%; animation-delay: 3.5s;"></div>
                <div class="sparkle sparkle-gold sparkle-large sparkle-float-2" style="top: 25%; right: 12%; animation-delay: 4.5s;"></div>
                <div class="sparkle sparkle-pink sparkle-medium sparkle-float-3" style="top: 5%; left: 25%; animation-delay: 0.5s;"></div>
                <div class="sparkle sparkle-purple sparkle-small sparkle-pulse" style="top: 30%; right: 25%; animation-delay: 1.2s;"></div>
                <div class="sparkle sparkle-gold sparkle-large sparkle-float-1" style="top: 60%; left: 20%; animation-delay: 2.7s;"></div>
                <div class="sparkle sparkle-pink sparkle-tiny sparkle-float-2" style="top: 85%; right: 18%; animation-delay: 3.2s;"></div>
                <div class="sparkle sparkle-purple sparkle-medium sparkle-float-3" style="top: 40%; left: 30%; animation-delay: 4.7s;"></div>
                <div class="sparkle sparkle-gold sparkle-small sparkle-pulse" style="top: 70%; right: 30%; animation-delay: 5.2s;"></div>
                <div class="sparkle sparkle-pink sparkle-large sparkle-float-1" style="top: 15%; left: 35%; animation-delay: 0.8s;"></div>
                <div class="sparkle sparkle-purple sparkle-tiny sparkle-float-2" style="top: 55%; right: 35%; animation-delay: 1.8s;"></div>
                <div class="sparkle sparkle-gold sparkle-medium sparkle-float-3" style="top: 90%; left: 40%; animation-delay: 2.3s;"></div>
                <div class="sparkle sparkle-pink sparkle-small sparkle-pulse" style="top: 25%; right: 40%; animation-delay: 3.8s;"></div>

                <!-- ENHANCED Floating Hearts with different animations -->
                <div class="heart heart-medium heart-float-up" style="left: 10%; animation-delay: 0s;">💕</div>
                <div class="heart heart-large heart-float-side" style="top: 30%; animation-delay: 2s;">💖</div>
                <div class="heart heart-small heart-pulse" style="top: 50%; left: 5%; animation-delay: 4s;">💗</div>
                <div class="heart heart-medium heart-float-up" style="left: 25%; animation-delay: 6s;">💝</div>
                <div class="heart heart-large heart-float-side" style="top: 40%; animation-delay: 8s;">💓</div>
                <div class="heart heart-small heart-pulse" style="top: 70%; left: 15%; animation-delay: 10s;">💘</div>
                <div class="heart heart-medium heart-float-up" style="left: 40%; animation-delay: 1s;">❤️</div>
                <div class="heart heart-large heart-float-side" style="top: 20%; animation-delay: 3s;">💞</div>
                <div class="heart heart-small heart-pulse" style="top: 60%; left: 30%; animation-delay: 5s;">💕</div>
                <div class="heart heart-medium heart-float-up" style="left: 60%; animation-delay: 7s;">💖</div>
                <div class="heart heart-large heart-float-side" style="top: 35%; animation-delay: 9s;">💗</div>
                <div class="heart heart-small heart-pulse" style="top: 80%; left: 20%; animation-delay: 11s;">💝</div>

                <!-- Hidden Audio -->
                <audio id="background-music" src="/audio/g2b-piano.mp3"></audio>
                
                <!-- Main Content Layer -->
                <div class="content-wrapper">
                    <div class="max-w-4xl mx-auto px-6 py-12 md:py-16 parallax-content" id="main-content">
                        <div id="gate" class="text-center">
                            <h1 class="text-4xl md:text-5xl font-serif italic text-pink-600 mb-8 fade-in-up float-animation" style="animation-delay: 0.2s; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                                A Letter From My Heart
                            </h1>
                            
                            <div class="glass glass-shimmer rounded-2xl p-8 mb-8 fade-in-up" style="animation-delay: 0.4s;">
                                <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                                    This webpage won&#39;t be here for long. So please take the time to try it out.
                                </p>
                                <p class="text-lg text-gray-700 leading-relaxed">
                                    You know who I am. You know that I&#39;m the only guy who&#39;d make a webpage just to tell you something. What&#39;s the one place you will ever really think of — the first place that comes to your mind when you think of me?
                                </p>
                            </div>

                            <div class="fade-in-up" style="animation-delay: 0.6s;">
                                <button id="music-button" onclick="playMusic()" class="btn-romantic btn-pulse text-white px-8 py-4 rounded-full text-lg font-medium mb-4 inline-flex items-center gap-3 glow-border">
                                    Play ▶
                                </button>
                                <p class="text-gray-500 italic text-sm mb-6">
                                    Click the above button to start the music before continuing
                                </p>
                                
                                <div class="glass glass-shimmer rounded-xl p-6 max-w-md mx-auto">
                                    <input type="text" id="password" placeholder="Enter the password..." disabled 
                                           class="w-full px-4 py-3 rounded-lg border border-pink-200 focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-transparent bg-white/80 text-center text-lg" />
                                    <button onclick="checkPassword()" class="btn-romantic text-white px-6 py-3 rounded-full mt-4 font-medium glow-border">
                                        Open My Heart
                                    </button>
                                    <p id="error" class="text-pink-600 mt-3 italic"></p>
                                </div>
                            </div>
                        </div>

                        <div id="letter" class="hidden">
                            <h1 class="text-4xl md:text-5xl font-serif italic text-pink-600 mb-12 text-center fade-in-up float-animation" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                                A Letter From My Heart
                            </h1>

                            <div class="space-y-8">
                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 0.2s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        Hi, I know – weird way to tell you something. But hey, this is the website I made for my OJT. I don&#39;t know until when this will be here but, if you&#39;re reading this – I want you to know that I love you. From the day I asked you if I could court you, until now. And I&#39;d always be willing to be there for you, always. From start to finish, day in or day out. No matter what.
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 0.4s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        I love the way you smile, the way you laugh, the way you talk, walk and how your eyes twinkle when you talk about something you love. Or the frustration in your voice when you talk to me about something that happened with your day. I love everything about you. Always have, always will. I&#39;m not willing to let us go, I&#39;ll be willing to make us right. Argue if we must, but I&#39;d always choose to make it right by you.
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 0.6s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        Because no other person has ever made me feel like I actually mattered. Or that there&#39;s something in me that is still worth loving. If anything, I don&#39;t ever want you to go. I know I sound selfish, and I know that I sound like a million other guys saying how much they want their girl to stay, but trust me – with my actions and my words that I show you – this is my vulnerable, honest and genuine self.
                                    </p>
                                </div>

                                <div class="text-center fade-in-up float-animation" style="animation-delay: 0.8s;">
                                    <p class="text-2xl font-bold text-pink-800">So please.</p>
                                </div>

                                <div class="w-full h-px bg-gradient-to-r from-transparent via-pink-500 to-transparent my-12 fade-in-up" style="animation-delay: 1s;"></div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 1.2s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        Your time, I respect. Your dreams, I wish for you. Your safety, is what matters to me. And your heart, pure and gold, the one thing I wish to take care of.
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 1.4s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        With you I am strong, with you I feel like I can be myself. For you, I would move a mountain or die trying. I&#39;d walk through hell and come back if you ask me to.
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 1.6s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        If anything, the small moments we share, be it when we call at night, or when we talk about each other&#39;s day, you don&#39;t know how much that means to me. Well, all that I&#39;m getting at, is, we&#39;ve been going at it good and well. I enjoy your company, you enjoy mine. We share laughter, happiness, even sadness, frustration, and all stuff like that.
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 1.8s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        I&#39;m sorry for crossing a line I shouldn&#39;t have by asking you or by pushing you to the limit. I really didn&#39;t mean to. Please let me make it up to you, and please give us a chance. I really, really don&#39;t want to lose you. But I respect that you want some breathing room and space. I totally do. Just, please give us a chance.
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 2s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        And if I&#39;ll have to wait for a while &#39;til time and fate decides that we can start over again, be it from the bottom up, I&#39;ll be there patiently waiting for that time to come. You&#39;ll always have a spot in my heart, beb. And my heart will never, ever be closed off for you.
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 bg-gradient-to-r from-pink-50 to-purple-50 border-pink-300 fade-in-up" style="animation-delay: 2.2s;">
                                    <blockquote class="text-lg italic text-gray-600 border-l-4 border-pink-500 pl-6">
                                        "So it&#39;s not gonna be easy. It&#39;s going to be really hard; we&#39;re gonna have to work at this everyday, but I want to do that because I want you. I want all of you, forever, everyday."
                                        <br><em class="text-sm mt-2 block">– Noah, The Notebook</em>
                                    </blockquote>
                                </div>

                                <div class="text-center space-y-6 fade-in-up float-animation" style="animation-delay: 2.4s;">
                                    <p class="text-xl font-bold text-pink-800">Meeting you was nothing short of magic. And I don&#39;t want to let that magic go. So if it&#39;ll take me days, weeks or months waiting for you.</p>
                                    <p class="text-xl font-bold text-pink-800">Then I&#39;ll gladly love you still. For being with you is not just a moment of bliss, but a happiness, a joy, and a feeling of freedom.</p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 2.6s;">
                                    <p class="text-lg leading-relaxed text-gray-700 text-center">
                                        In all ways, through all of time, always.
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 2.8s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        I love you, beb. Now go listen to how I play "Got to Believe in Magic."
                                    </p>
                                </div>

                                <div class="glass glass-shimmer rounded-2xl p-8 fade-in-up" style="animation-delay: 3s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        Because what I feel for you? The odds of us meeting? It&#39;s like a miracle of magic. And I&#39;m not going to let that go.
                                    </p>
                                </div>

                                <footer class="text-right mt-12 italic text-gray-600 fade-in-up" style="animation-delay: 3.2s;">
                                    <div class="glass glass-shimmer rounded-xl p-6 inline-block glow-border">
                                        Through thick and thin,<br>
                                        <span class="text-pink-600 font-semibold">Dian.</span>
                                    </div>
                                </footer>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    const audio = document.getElementById("background-music");
                    const passwordInput = document.getElementById("password");
                    const musicButton = document.getElementById("music-button");
                    const errorElement = document.getElementById("error");
                    const mainContent = document.getElementById("main-content");
                    const parallaxContainer = document.getElementById("parallax-container");
                    const layer1 = document.getElementById("layer1");
                    const layer2 = document.getElementById("layer2");
                    const layer3 = document.getElementById("layer3");
                    const cloud1 = document.getElementById("cloud1");
                    const cloud2 = document.getElementById("cloud2");
                    const cloud3 = document.getElementById("cloud3");
                    const cloud4 = document.getElementById("cloud4");
                    
                    // DRAMATICALLY ENHANCED PARALLAX EFFECT
                    window.addEventListener("mousemove", (e) => {
                        const x = e.clientX / window.innerWidth;
                        const y = e.clientY / window.innerHeight;
                        
                        // Move the background layers in opposite directions for dramatic effect
                        layer1.style.transform = `translateZ(-200px) scale(1.5) translate(${x * -100}px, ${y * -100}px)`;
                        layer2.style.transform = `translateZ(-400px) scale(2) translate(${x * 150}px, ${y * 150}px)`;
                        layer3.style.transform = `translateZ(-600px) scale(2.5) translate(${x * -200}px, ${y * -200}px)`;
                        
                        // Move clouds for additional depth
                        cloud1.style.transform = `translate(${x * -80}px, ${y * -80}px)`;
                        cloud2.style.transform = `translate(${x * 120}px, ${y * 120}px)`;
                        cloud3.style.transform = `translate(${x * -150}px, ${y * -150}px)`;
                        cloud4.style.transform = `translate(${x * 100}px, ${y * 100}px)`;
                        
                        // Move the content slightly for subtle parallax
                        mainContent.style.transform = `translate(${x * 20}px, ${y * 20}px)`;
                    });
                    
                    // Enhanced scroll parallax - FIXED REGEX ERROR
                    window.addEventListener("scroll", () => {
                        const scrolled = window.pageYOffset;
                        const rate1 = scrolled * 0.8;
                        const rate2 = scrolled * -0.5;
                        const rate3 = scrolled * 0.3;
                        
                        // Apply different scroll speeds to each layer - FIXED REGEX
                        layer1.style.transform = layer1.style.transform.replace(/translateY\$$[^)]+\$$/, "") + ` translateY(${rate1}px)`;
                        layer2.style.transform = layer2.style.transform.replace(/translateY\$$[^)]+\$$/, "") + ` translateY(${rate2}px)`;
                        layer3.style.transform = layer3.style.transform.replace(/translateY\$$[^)]+\$$/, "") + ` translateY(${rate3}px)`;
                        
                        // Apply scroll effect to clouds - FIXED REGEX
                        cloud1.style.transform = cloud1.style.transform.replace(/translateY\$$[^)]+\$$/, "") + ` translateY(${scrolled * 0.2}px)`;
                        cloud2.style.transform = cloud2.style.transform.replace(/translateY\$$[^)]+\$$/, "") + ` translateY(${scrolled * -0.3}px)`;
                        cloud3.style.transform = cloud3.style.transform.replace(/translateY\$$[^)]+\$$/, "") + ` translateY(${scrolled * 0.4}px)`;
                        cloud4.style.transform = cloud4.style.transform.replace(/translateY\$$[^)]+\$$/, "") + ` translateY(${scrolled * -0.5}px)`;
                    });

                    // ENHANCED SPARKLE GENERATION with different types
                    function createSparkle() {
                        const sparkle = document.createElement("div");
                        
                        // Randomize sparkle size
                        const sizes = ["sparkle-tiny", "sparkle-small", "sparkle-medium", "sparkle-large"];
                        const randomSize = sizes[Math.floor(Math.random() * sizes.length)];
                        
                        // Randomize sparkle color
                        const colors = ["sparkle-gold", "sparkle-pink", "sparkle-purple"];
                        const randomColor = colors[Math.floor(Math.random() * colors.length)];
                        
                        // Randomize sparkle animation
                        const animations = ["sparkle-float-1", "sparkle-float-2", "sparkle-float-3", "sparkle-pulse"];
                        const randomAnimation = animations[Math.floor(Math.random() * animations.length)];
                        
                        sparkle.className = `sparkle ${randomSize} ${randomColor} ${randomAnimation}`;
                        
                        // Position randomly but favor the sides
                        const side = Math.random() > 0.5;
                        const xPos = side ? 
                            Math.random() * 30 + "%" : 
                            (Math.random() * 30 + 70) + "%";
                        
                        sparkle.style.left = xPos;
                        sparkle.style.top = Math.random() * 100 + "%";
                        sparkle.style.animationDelay = Math.random() * 5 + "s";
                        sparkle.style.animationDuration = (Math.random() * 6 + 4) + "s";
                        
                        document.body.appendChild(sparkle);
                        
                        // Remove after animation completes
                        setTimeout(() => {
                            sparkle.remove();
                        }, 10000);
                    }

                    // Create sparkles more frequently
                    setInterval(createSparkle, 300);
                    
                    // NEW: Create star sparkles
                    function createStar() {
                        const star = document.createElement("div");
                        star.className = "star";
                        star.textContent = "✨";
                        star.style.left = Math.random() * 100 + "%";
                        star.style.top = Math.random() * 100 + "%";
                        star.style.animationDelay = Math.random() * 4 + "s";
                        document.body.appendChild(star);
                        
                        setTimeout(() => {
                            star.remove();
                        }, 8000);
                    }
                    
                    // Create stars periodically
                    setInterval(createStar, 2000);
                    
                    // ENHANCED HEART GENERATION with different animations
                    function createHeart() {
                        const heart = document.createElement("div");
                        
                        // Randomize heart size
                        const sizes = ["heart-small", "heart-medium", "heart-large"];
                        const randomSize = sizes[Math.floor(Math.random() * sizes.length)];
                        
                        // Randomize heart emoji
                        const hearts = ["❤️", "💖", "💗", "💓", "💘", "💝", "💞", "💕"];
                        const randomHeart = hearts[Math.floor(Math.random() * hearts.length)];
                        
                        // Randomize heart animation
                        const animations = ["heart-float-up", "heart-float-side", "heart-pulse"];
                        const randomAnimation = animations[Math.floor(Math.random() * animations.length)];
                        
                        heart.className = `heart ${randomSize} ${randomAnimation}`;
                        heart.textContent = randomHeart;
                        
                        if (randomAnimation === "heart-float-up") {
                            // Start from bottom of screen
                            heart.style.left = Math.random() * 100 + "%";
                            heart.style.bottom = "-50px";
                            heart.style.top = "auto";
                        } else if (randomAnimation === "heart-float-side") {
                            // Start from left side
                            heart.style.left = "-50px";
                            heart.style.top = Math.random() * 100 + "%";
                        } else {
                            // Pulse animation - position randomly
                            heart.style.left = Math.random() * 100 + "%";
                            heart.style.top = Math.random() * 100 + "%";
                        }
                        
                        // Randomize animation properties
                        const duration = Math.random() * 10 + 10; // 10-20 seconds
                        const delay = Math.random() * 5;
                        
                        heart.style.animationDuration = duration + "s";
                        heart.style.animationDelay = delay + "s";
                        
                        document.body.appendChild(heart);
                        
                        // Remove after animation completes
                        setTimeout(() => {
                            heart.remove();
                        }, (duration + delay) * 1000);
                    }

                    // Create hearts frequently
                    setInterval(createHeart, 800);

                    // Add event listener for Enter key
                    passwordInput.addEventListener("keydown", function(event) {
                        if (event.key === "Enter") {
                            checkPassword();
                        }
                    });

                    function playMusic() {
                        audio.play()
                            .then(() => {
                                passwordInput.disabled = false;
                                passwordInput.focus();
                                musicButton.style.display = "none";
                                document.querySelector(".text-gray-500").style.display = "none";
                                
                                // Create a burst of sparkles when music starts
                                for (let i = 0; i < 30; i++) {
                                    setTimeout(createSparkle, i * 50);
                                }
                                
                                // Create a burst of hearts when music starts
                                for (let i = 0; i < 15; i++) {
                                    setTimeout(createHeart, i * 100);
                                }
                                
                                // Create a burst of stars when music starts
                                for (let i = 0; i < 10; i++) {
                                    setTimeout(createStar, i * 150);
                                }
                            })
                            .catch(err => {
                                alert("Please interact with the page first to enable music.");
                                console.error("Autoplay prevented:", err);
                            });
                    }

                    function checkPassword() {
                        const correct = "Gabawan";
                        const input = passwordInput.value.trim();

                        if (input.toLowerCase() === correct.toLowerCase()) {
                            document.getElementById("gate").style.display = "none";
                            document.getElementById("letter").style.display = "block";

                            // Ensure music continues playing
                            if (audio.paused) {
                                audio.play();
                            }
                            
                            // Create a massive burst of sparkles, hearts and stars when the letter opens
                            for (let i = 0; i < 50; i++) {
                                setTimeout(createSparkle, i * 30);
                            }
                            
                            for (let i = 0; i < 25; i++) {
                                setTimeout(createHeart, i * 60);
                            }
                            
                            for (let i = 0; i < 20; i++) {
                                setTimeout(createStar, i * 80);
                            }
                        } else {
                            errorElement.textContent = "Try again. It\'s where the fireworks first sparked.";
                            errorElement.style.animation = "fadeInUp 0.5s ease forwards";

                            // Shake the input field for wrong password
                            passwordInput.style.animation = "none";
                            setTimeout(() => {
                                passwordInput.style.animation = "shake 0.5s ease";
                            }, 10);
                        }
                    }

                    // Add shake animation for wrong password
                    document.head.insertAdjacentHTML("beforeend", `
                        <style>
                            @keyframes shake {
                                0%, 100% { transform: translateX(0); }
                                20%, 60% { transform: translateX(-5px); }
                                40%, 80% { transform: translateX(5px); }
                            }
                        </style>
                    `);
                    
                    // Initialize with some sparkles, hearts and stars
                    window.addEventListener("load", () => {
                        // Initial sparkles
                        for (let i = 0; i < 30; i++) {
                            setTimeout(createSparkle, i * 100);
                        }
                        
                        // Initial hearts
                        for (let i = 0; i < 15; i++) {
                            setTimeout(createHeart, i * 200);
                        }
                        
                        // Initial stars
                        for (let i = 0; i < 10; i++) {
                            setTimeout(createStar, i * 300);
                        }
                        
                        // Add subtle movement to parallax container on load
                        setTimeout(() => {
                            parallaxContainer.style.transition = "transform 2s ease-in-out";
                            parallaxContainer.style.transform = "translateY(-10px)";
                            
                            setTimeout(() => {
                                parallaxContainer.style.transform = "translateY(10px)";
                                
                                // Set up continuous subtle floating motion
                                setInterval(() => {
                                    parallaxContainer.style.transform = 
                                        parallaxContainer.style.transform === "translateY(-10px)" ? 
                                        "translateY(10px)" : "translateY(-10px)";
                                }, 2000);
                            }, 2000);
                        }, 500);
                    });
                </script>
            </body>
        </html>
    ';
});