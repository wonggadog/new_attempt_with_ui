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
                    }
                    @keyframes gradientBG {
                        0% { background-position: 0% 50%; }
                        50% { background-position: 100% 50%; }
                        100% { background-position: 0% 50%; }
                    }
                    /* Parallax layers */
                    .parallax-layer {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        pointer-events: none;
                    }
                    .parallax-bg {
                        background: radial-gradient(circle at 20% 80%, rgba(255, 182, 193, 0.3) 0%, transparent 50%),
                                    radial-gradient(circle at 80% 20%, rgba(255, 192, 203, 0.3) 0%, transparent 50%),
                                    radial-gradient(circle at 40% 40%, rgba(255, 228, 225, 0.2) 0%, transparent 50%);
                        animation: parallaxFloat 20s ease-in-out infinite;
                    }
                    @keyframes parallaxFloat {
                        0%, 100% { transform: translateY(0px) rotate(0deg); }
                        33% { transform: translateY(-10px) rotate(1deg); }
                        66% { transform: translateY(5px) rotate(-1deg); }
                    }
                    /* Floating sparkles */
                    .sparkle {
                        position: absolute;
                        width: 4px;
                        height: 4px;
                        background: linear-gradient(45deg, #ffd700, #ffed4e);
                        border-radius: 50%;
                        animation: sparkleFloat 6s ease-in-out infinite;
                        box-shadow: 0 0 6px #ffd700;
                    }
                    .sparkle:before {
                        content: "";
                        position: absolute;
                        top: -1px;
                        left: -1px;
                        right: -1px;
                        bottom: -1px;
                        background: linear-gradient(45deg, #ffd700, #ffed4e);
                        border-radius: 50%;
                        z-index: -1;
                        filter: blur(1px);
                    }
                    @keyframes sparkleFloat {
                        0%, 100% { 
                            transform: translateY(0px) translateX(0px) scale(1);
                            opacity: 0.7;
                        }
                        25% { 
                            transform: translateY(-20px) translateX(10px) scale(1.2);
                            opacity: 1;
                        }
                        50% { 
                            transform: translateY(-10px) translateX(-5px) scale(0.8);
                            opacity: 0.8;
                        }
                        75% { 
                            transform: translateY(-30px) translateX(15px) scale(1.1);
                            opacity: 0.9;
                        }
                    }
                    /* Content with parallax */
                    .content-layer {
                        position: relative;
                        z-index: 10;
                        transform-style: preserve-3d;
                    }
                    .parallax-content {
                        transform: translateZ(0);
                        will-change: transform;
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
                    /* Floating hearts */
                    .heart {
                        position: absolute;
                        color: rgba(255, 182, 193, 0.6);
                        font-size: 20px;
                        animation: heartFloat 8s ease-in-out infinite;
                        pointer-events: none;
                    }
                    @keyframes heartFloat {
                        0%, 100% { 
                            transform: translateY(0px) rotate(0deg);
                            opacity: 0.3;
                        }
                        50% { 
                            transform: translateY(-50px) rotate(10deg);
                            opacity: 0.7;
                        }
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
                    /* Glass morphism effect */
                    .glass {
                        background: rgba(255, 255, 255, 0.25);
                        backdrop-filter: blur(10px);
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                    }
                    /* Responsive adjustments */
                    @media (max-width: 768px) {
                        .sparkle {
                            width: 3px;
                            height: 3px;
                        }
                        .heart {
                            font-size: 16px;
                        }
                    }
                </style>
            </head>
            <body class="relative">
                <!-- Parallax Background Layer -->
                <div class="parallax-layer parallax-bg"></div>
                <!-- Floating Sparkles -->
                <div class="sparkle" style="top: 10%; left: 5%; animation-delay: 0s;"></div>
                <div class="sparkle" style="top: 20%; right: 8%; animation-delay: 1s;"></div>
                <div class="sparkle" style="top: 35%; left: 3%; animation-delay: 2s;"></div>
                <div class="sparkle" style="top: 50%; right: 5%; animation-delay: 3s;"></div>
                <div class="sparkle" style="top: 65%; left: 7%; animation-delay: 4s;"></div>
                <div class="sparkle" style="top: 80%; right: 10%; animation-delay: 5s;"></div>
                <div class="sparkle" style="top: 15%; left: 15%; animation-delay: 1.5s;"></div>
                <div class="sparkle" style="top: 45%; right: 15%; animation-delay: 2.5s;"></div>
                <div class="sparkle" style="top: 75%; left: 12%; animation-delay: 3.5s;"></div>
                <div class="sparkle" style="top: 25%; right: 12%; animation-delay: 4.5s;"></div>
                <!-- Floating Hearts -->
                <div class="heart" style="top: 15%; left: 10%; animation-delay: 0s;">💕</div>
                <div class="heart" style="top: 40%; right: 15%; animation-delay: 2s;">💖</div>
                <div class="heart" style="top: 70%; left: 8%; animation-delay: 4s;">💗</div>
                <div class="heart" style="top: 30%; right: 8%; animation-delay: 6s;">💝</div>
                <!-- Hidden Audio -->
                <audio id="background-music" src="/audio/piano.mp3"></audio>
                <!-- Main Content Layer -->
                <div class="content-layer relative z-10">
                    <div class="max-w-4xl mx-auto px-6 py-12 md:py-16 parallax-content">
                        <div id="gate" class="text-center">
                            <h1 class="text-4xl md:text-5xl font-serif italic text-pink-600 mb-8 fade-in-up" style="animation-delay: 0.2s; text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                                A Letter From My Heart
                            </h1>
                            <div class="glass rounded-2xl p-8 mb-8 fade-in-up" style="animation-delay: 0.4s;">
                                <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                                    This webpage won&#39;t be here for long. So please take the time to try it out.
                                </p>
                                <p class="text-lg text-gray-700 leading-relaxed">
                                    You know who I am. You know that I&#39;m the only guy who&#39;d go as far as to make a website to tell you something.<br>What&#39;s the one place you will ever really think of — the first place that comes to your mind when you think of me?
                                </p>
                            </div>
                            <div class="fade-in-up" style="animation-delay: 0.6s;">
                                <button id="music-button" onclick="playMusic()" class="btn-romantic text-white px-8 py-4 rounded-full text-lg font-medium mb-4 inline-flex items-center gap-3">
                                    Play ▶
                                </button>
                                <p class="text-gray-500 italic text-sm mb-6">
                                    Click the above button to start the music before continuing
                                </p>
                                <div class="glass rounded-xl p-6 max-w-md mx-auto">
                                    <input type="text" id="password" placeholder="Enter the password..." disabled 
                                           class="w-full px-4 py-3 rounded-lg border border-pink-200 focus:outline-none focus:ring-2 focus:ring-pink-300 focus:border-transparent bg-white/80 text-center text-lg" />
                                    <button onclick="checkPassword()" class="btn-romantic text-white px-6 py-3 rounded-full mt-4 font-medium">
                                        Submit
                                    </button>
                                    <p id="error" class="text-pink-600 mt-3 italic"></p>
                                </div>
                            </div>
                        </div>
                        <div id="letter" class="hidden">
                            <h1 class="text-4xl md:text-5xl font-serif italic text-pink-600 mb-12 text-center fade-in-up" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.1);">
                                A Letter From My Heart
                            </h1>
                            <div class="space-y-8">
                                <!-- Single Box for All Paragraphs -->
                                <div class="glass rounded-2xl p-8 fade-in-up" style="animation-delay: 0.2s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        Hi, I know – unconventional way that someone would tell you something, and you did say no more flowery words. But it&#39;s one of the only things I&#39;m good at. And I hope you bear with me to the end with this haha. But hey, I made this during my OJT. I kept this website hidden inside the codes. I don&#39;t know until when this will be here, people from ICTO will probably take this down sometime soon, but, I will be updating this every two days or so. Visit this website every now and then, yeah? Hahaha.<br>
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">                            
                                        And, let me say this now, – I want you to know that I love you. From the day I asked you if I could court you, until now. And, hear me out, I&#39;m always willing to be there for you, always. From start to finish, day in or day out.
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        Okay, so here&#39;s the plethora of thigns I really wanted to tell you. First of all- I&#39;m sorry. For having pressured you with expectations that you were overwhelmed with the idea that you can&#39;t deliver them. I really do feel bad at that. The last thing you needed was a relationship that expects a lot in return from you. I didn&#39;t really mean no harm, honest. And I really am not asking so much from you to fill my expectations. But passively and indirectly, that&#39;s what I did. And now I&39;ve filled you too much. You don&#39;t deserve that chaos in your mind. From the bottom of my hear I&#39;m sorry about that, pretty.        
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        I&#39;ll be honest, I&#39;m not the best guy in the world. I&#39;m not the most romantic, or the most charming, or the most handsome. But I do know that I love you. And I know that I want to be with you. I want to be there for you, to support you, to make you happy, and to love you with all my heart. 
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        I love the way you smile, the way you laugh, the way you talk, walk and how your eyes twinkle when you talk about something you love. Or the frustration in your voice when you talk to me about something that happened with your day. I love everything about you. Always have, always will. I&#39;m not willing to let us go, I&#39;ll be willing to make us right. Argue if we must, but I&#39;d always choose to make it right by you.
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        Because no other person has ever made me feel like I actually mattered. Or that there&#39;s something in me that is still worth loving. If anything, I don&#39;t ever want you to go. I know I sound selfish, and I know that I sound like a million other guys saying how much they want their girl to stay, but trust me – with my actions and my words that I show you – this is my vulnerable, honest and genuine self.
                                    </p>

                                    <!-- <div class="text-center mt-8">
                                        <p class="text-2xl font-bold text-pink-800">So please.</p>
                                    </div> -->

                                    <div class="w-full h-px bg-gradient-to-r from-transparent via-pink-500 to-transparent my-12"></div>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        Your time, I respect. Your dreams, I wish for you. Your safety, is what matters to me. And your heart, pure and gold, the one thing I wish to take care of.
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        With you I am strong, with you I feel like I can be myself. For you, I would move a mountain or die trying. I&#39;d walk through hell and come back if you ask me to.
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        If anything, the small moments we share, be it when we call at night, or when we talk about each other&#39;s day, you don&#39;t know how much that means to me. Well, all that I&#39;m getting at, is, we&#39;ve been going at it good and well. I enjoy your company, you enjoy mine. We share laughter, happiness, even sadness, frustration, and all stuff like that.
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        I&#39;m sorry for crossing a line I shouldn&#39;t have by asking you or by pushing you to the limit. I really didn&#39;t mean to. Please let me make it up to you, and please give us a chance. I really, really don&#39;t want to lose you. But I respect that you want some breathing room and space. I totally do. Just, please give us a chance.
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        You&#39;re not too much. You&#39;re not too sensitive. You&#39;re not too emotional. You&#39;re not too sensitive. You&#39;re just you, and you feel things deeply. I love you for that, and it&#39;s a gift. Don&#39;t ever apologize for how you feel especially when it&#39;s you who got hurt. People who are worth your time will appreciate hoe much you care.  
                                    </p>

                                    <p class="text-lg leading-relaxed text-gray-700 indent-8 mt-6">
                                        And if I&#39;ll have to wait for a while &#39;til time and fate decides that we can start over again, be it from the bottom up, I&#39;ll be there patiently waiting for that time to come. You&#39;ll always have a spot in my heart, beb. And my heart will never, ever be closed off for you. I&#39m not giving up on you, you&#39re worth it, I&#39m gonna treat you the way you  deserve,  I&#39m gonna learn how to love you right. I&#39m not going anywhere.
                                    </p>
                                </div>

                                <!-- Quote Block (Separate) -->
                                <div class="glass rounded-2xl p-8 bg-gradient-to-r from-pink-50 to-purple-50 border-pink-300 fade-in-up" style="animation-delay: 2.2s;">
                                    <blockquote class="text-lg italic text-gray-600 border-l-4 border-pink-500 pl-6">
                                        "So it&#39;s not gonna be easy. It&#39;s going to be really hard; we&#39;re gonna have to work at this everyday, but I want to do that because I want you. I want all of you, forever, everyday."
                                        <br><em class="text-sm mt-2 block">– Noah, The Notebook</em>
                                    </blockquote>
                                </div>

                                <!-- Remaining Sections -->
                                <div class="text-center space-y-6 fade-in-up" style="animation-delay: 2.4s;">
                                    <p class="text-xl font-bold text-pink-800">Meeting you was nothing short of magic. And I don&#39;t want to let that magic go, even for years to come. So if it&#39;ll take me days, weeks or months waiting for you,<br>then I&#39;ll gladly love you still. For being with you is not just a moment of bliss, but a happiness, a joy, and a feeling of freedom.</p>
                                </div>
                                <div class="glass rounded-2xl p-8 fade-in-up" style="animation-delay: 2.6s;">
                                    <p class="text-lg leading-relaxed text-gray-700 text-center">
                                        In all ways, through all of time, always.
                                    </p>
                                </div>
                                <div class="glass rounded-2xl p-8 fade-in-up" style="animation-delay: 2.8s;">
                                    <p class="text-lg leading-relaxed text-gray-700 text-center">
                                        I love you, beb. Now here I am dedicating to you my playing of "Can&#39;t Help Falling in Love" as I myself can&#39;t help but fall in love with you over and over again. I hope you enjoy it as much as I enjoyed making this website for you. Be sure to finish the music ahh? Hahaha
                                    </p>
                                </div>
                                <div class="glass rounded-2xl p-8 fade-in-up" style="animation-delay: 3s;">
                                    <p class="text-lg leading-relaxed text-gray-700 indent-8">
                                        This chapter may have ended. But I&#39;m not closing the book. I&#39;m just putting it down for a while. I&#39;ll be here, waiting for you to pick it up again. And when you do, I&#39;ll be ready to write the next chapter with you.
                                    </p>
                                </div>
                                <footer class="text-right mt-12 italic text-gray-600 fade-in-up" style="animation-delay: 3.2s;">
                                    <div class="glass rounded-xl p-6 inline-block">
                                        Through thick and thin,<br>
                                        <span class="text-pink-600 font-semibold">Dian.</span>
                                    </div>
                                </footer>
                                <br><br><br>
                                <footer class="text-left mt-12 text-gray-600 fade-in-up" style="animation-delay: 3.2s;">
                                    <div class="glass rounded-xl p-6 inline-block">
                                        PS: Want to watch Bicol Loco festival again with me if you&#39;re free, just like last year?<br>Word sad there are going to be MORE hot air balloons hahaha. Let me know if ever ;))<br>Stay tuned to this website for updates! Haha
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

                    window.addEventListener("scroll", () => {
                        const scrolled = window.pageYOffset;
                        const parallaxElements = document.querySelectorAll(".parallax-content");
                        const speed = 0.5;
                        parallaxElements.forEach(element => {
                            const yPos = -(scrolled * speed);
                            element.style.transform = `translateY(${yPos}px)`;
                        });
                    });

                    function createSparkle() {
                        const sparkle = document.createElement("div");
                        sparkle.className = "sparkle";
                        sparkle.style.left = Math.random() * 100 + "%";
                        sparkle.style.top = Math.random() * 100 + "%";
                        sparkle.style.animationDelay = Math.random() * 6 + "s";
                        document.body.appendChild(sparkle);
                        setTimeout(() => {
                            sparkle.remove();
                        }, 6000);
                    }

                    setInterval(createSparkle, 3000);

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
                            if (audio.paused) {
                                audio.play();
                            }
                        } else {
                            errorElement.textContent = "Try again. It&#39;s where the fireworks first sparked.";
                            errorElement.style.animation = "fadeInUp 0.5s ease forwards";
                            passwordInput.style.animation = "none";
                            setTimeout(() => {
                                passwordInput.style.animation = "shake 0.5s ease";
                            }, 10);
                        }
                    }

                    document.head.insertAdjacentHTML("beforeend", `
                        <style>
                            @keyframes shake {
                                0%, 100% { transform: translateX(0); }
                                20%, 60% { transform: translateX(-5px); }
                                40%, 80% { transform: translateX(5px); }
                            }
                        </style>
                    `);
                </script>
            </body>
        </html>
    ';
});