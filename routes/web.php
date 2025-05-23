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
                <title>Through all time</title>
                <style>
                    /* Animated Gradient Background */
                    body {
                        font-family: "Georgia", serif;
                        margin: 0;
                        padding: 0;
                        min-height: 100vh;
                        background: linear-gradient(-45deg, #ffecf2, #ffe6eb, #ffe0f0, #fde5ff);
                        background-size: 400% 400%;
                        animation: gradientBG 10s ease infinite;
                        color: #3a2d3d;
                        line-height: 1.9;
                        text-align: justify;
                    }
                    @keyframes gradientBG {
                        0% { background-position: 0% 50%; }
                        50% { background-position: 100% 50%; }
                        100% { background-position: 0% 50%; }
                    }
                    .container {
                        max-width: 700px;
                        margin: auto;
                        padding: 3rem 2rem;
                    }
                    h1 {
                        text-align: center;
                        font-style: italic;
                        color: #d63384;
                        margin-bottom: 1.5rem;
                        font-size: 2rem;
                        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                    }
                    p {
                        margin-bottom: 1.5rem;
                        text-indent: 1em;
                        opacity: 0;
                        transform: translateY(20px);
                        animation: fadeInUp 1s ease forwards;
                    }
                    /* Delay each paragraph slightly */
                    p:nth-child(1) { animation-delay: 0.2s; }
                    p:nth-child(2) { animation-delay: 0.4s; }
                    p:nth-child(3) { animation-delay: 0.6s; }
                    p:nth-child(4) { animation-delay: 0.8s; }
                    p:nth-child(5) { animation-delay: 1s; }
                    p:nth-child(6) { animation-delay: 1.2s; }
                    p:nth-child(7) { animation-delay: 1.4s; }
                    p:nth-child(8) { animation-delay: 1.6s; }
                    p:nth-child(9) { animation-delay: 1.8s; }
                    p:nth-child(10) { animation-delay: 2s; }
                    p:nth-child(11) { animation-delay: 2.2s; }
                    p:nth-child(12) { animation-delay: 2.4s; }
                    p:nth-child(13) { animation-delay: 2.6s; }
                    p:nth-child(14) { animation-delay: 2.8s; }
                    p:nth-child(15) { animation-delay: 3s; }
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
                        background-color: rgba(255, 255, 255, 0.3);
                        padding: 1rem;
                        border-radius: 0 8px 8px 0;
                    }
                    footer {
                        text-align: right;
                        margin-top: 2rem;
                        font-style: italic;
                        opacity: 0;
                        animation: fadeInUp 1s ease forwards;
                        animation-delay: 3.2s;
                    }
                    hr {
                        border: 0;
                        height: 1px;
                        background: linear-gradient(to right, transparent, #d63384, transparent);
                        margin: 3rem 0;
                        opacity: 0;
                        animation: fadeInUp 1s ease forwards;
                        animation-delay: 1s;
                    }
                    #letter {
                        display: none;
                    }
                    .center {
                        text-align: center;
                        margin-top: 2rem;
                    }
                    input[type="text"] {
                        padding: 0.75rem 1rem;
                        width: 80%;
                        max-width: 300px;
                        margin-top: 1rem;
                        font-size: 1rem;
                        border-radius: 8px;
                        border: 1px solid #ffb3c8;
                        background-color: rgba(255, 255, 255, 0.8);
                        box-shadow: 0 2px 10px rgba(214, 51, 132, 0.1);
                        transition: all 0.3s ease;
                    }
                    input[type="text"]:focus {
                        outline: none;
                        border-color: #d63384;
                        box-shadow: 0 2px 15px rgba(214, 51, 132, 0.2);
                    }
                    button {
                        margin-top: 1rem;
                        padding: 0.75rem 1.75rem;
                        font-size: 1rem;
                        border: none;
                        background-color: #ff8fab;
                        color: white;
                        border-radius: 50px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 10px rgba(214, 51, 132, 0.2);
                    }
                    button:hover {
                        background-color: #ff6f91;
                        transform: translateY(-3px);
                        box-shadow: 0 6px 15px rgba(214, 51, 132, 0.3);
                    }
                    #error {
                        color: #d63384;
                        margin-top: 1rem;
                        font-style: italic;
                    }
                    #music-button {
                        background-color: #d63384;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto;
                    }
                    #music-button:hover {
                        background-color: #c2185b;
                    }
                    .note {
                        color: #888;
                        font-size: 0.9rem;
                        margin-top: 0.5rem;
                        font-style: italic;
                    }
                    /* Fade In Animation */
                    @keyframes fadeInUp {
                        from {
                            opacity: 0;
                            transform: translateY(20px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    /* Responsive adjustments */
                    @media (max-width: 768px) {
                        .container {
                            padding: 2rem 1.5rem;
                        }
                        h1 {
                            font-size: 1.75rem;
                        }
                        p {
                            font-size: 0.95rem;
                        }
                    }
                </style>
            </head>
            <body>
                <!-- Hidden Audio -->
                <audio id="background-music" src="/audio/g2b-piano.mp3"></audio>
                <div class="container">
                    <div id="gate">
                        <h1>A Letter From My Heart</h1>
                        <p>This webpage won&#39;t be here for long. So please take the time to try it out.</p>
                        <p>You know that I&#39;m the only guy who&#39;d make a webpage just to tell you how he feels and show you how serious he is for you. What&#39;s the one place you will ever really think of — the first place that comes to your mind when you think of me?</p>
                        <div class="center">
                            <button id="music-button" onclick="playMusic()">🎵 Play Piano Music</button>
                            <p class="note">Click the above button to start the music before continuing</p>
                            <br>
                            <input type="text" id="password" placeholder="Enter the password..." disabled />
                            <br>
                            <button onclick="checkPassword()">Open</button>
                            <p id="error"></p>
                        </div>
                    </div>
                    <div id="letter">
                        <h1>A Letter From My Heart</h1>
                        <p>Hi, I know – weird way to tell you something. But hey, this is the website I made for my OJT. I don&#39;t know until when this will be here but, if you&#39;re reading this – I want you to know that I love you. From the day I asked you if I could court you, until now. And I&#39;d always be willing to be there for you, always. From start to finish, day in or day out. No matter what.</p>
                        <p>I love the way you smile, the way you laugh, the way you talk, walk and how your eyes twinkle when you talk about something you love. Or the frustration in your voice when you talk to me about something that happened with your day. I love everything about you. Always have, always will. I&#39;m not willing to let us go, I&#39;ll be willing to make us right. Argue if we must, but I&#39;d always choose to make it right by you.</p>
                        <p>Because no other person has ever made me feel like I actually mattered. Or that there&#39;s something in me that is still worth loving. If anything, I don&#39;t ever want you to go. I know I sound selfish, and I know that I sound like a million other guys saying how much they want their girl to stay, but trust me – with my actions and my words that I show you – this is my vulnerable, honest and genuine self.</p>
                        <p><strong>So please.</strong></p>
                        <hr>
                        <p>Your time, I respect. Your dreams, I wish for you. Your safety, is what matters to me. And your heart, pure and gold, the one thing I wish to take care of.</p>
                        <p>With you I am strong, with you I feel like I can be myself. For you, I would move a mountain or die trying. I&#39;d walk through hell and come back if you ask me to.</p>
                        <p>I&#39;m sorry for crossing a line I shouldn&#39;t have by asking you or by pushing you to the limit. I really didn&#39;t mean to. Please let me make it up to you, and please give us a chance. I really, really don&#39;t want to lose you. But I respect that you want some breathing room and space. I totally do. Just, please give us a chance.</p>
                        <p>If anything, the small moments we share, be it when we call at night, or when we talk about each other&#39;s day, you don&#39;t know how much that means to me. Well, all that I&#39;m getting at, is, we&#39;ve been going at it good and well. I enjoy your company, you enjoy mine. We share laughter, happiness, even sadness, frustration, and all stuff like that.</p>
                        <blockquote>
                            "So it&#39;s not gonna be easy. It&#39;s going to be really hard; we&#39;re gonna have to work at this everyday, but I want to do that because I want you. I want all of you, forever, everyday."
                            <br><em>– Noah, The Notebook</em>
                        </blockquote>
                        <p>And no other words I say would ever explain better what I feel. But what I feel weighs so much more than the romance in The Notebook.</p>
                        <p><strong>So please – don&#39;t go.</strong></p>
                        <p>In all ways, through all of time, always.</p>
                        <p>I love you, beb. Now go listen to how I play "Got to Believe in Magic."</p>
                        <p>Because what I feel for you? The odds of us meeting? It&#39;s like a miracle of magic. And I&#39;m not going to let that go.</p>
                        <footer>
                            Through thick and thin,<br>Dian.
                        </footer>
                    </div>
                </div>
                <script>
                    const audio = document.getElementById("background-music");
                    const passwordInput = document.getElementById("password");
                    const musicButton = document.getElementById("music-button");
                    const musicNote = document.querySelector(".note");
                    const errorElement = document.getElementById("error");
                    // Add event listener for Enter key
                    passwordInput.addEventListener("keydown", function(event) {
                        if (event.key === "Enter") {
                            checkPassword();
                        }
                    });
                    function playMusic() {
                        audio.play()
                            .then(() => {
                                // Enable input once music starts
                                passwordInput.disabled = false;
                                passwordInput.focus();
                                musicButton.style.display = "none";
                                musicNote.style.display = "none";
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
                </script>
            </body>
        </html>
    ';
});