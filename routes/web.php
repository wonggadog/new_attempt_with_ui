<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommunicationFormController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminControlsController;

// Authentication Routes
Auth::routes();

// Guest Routes (Accessible without authentication)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Protected Routes (Require authentication)
Route::middleware('auth')->group(function () {
    // Home page displaying the communication form
    Route::get('/', [CommunicationFormController::class, 'index'])->name('home');

    // Form submission
    Route::post('/submit-form', [CommunicationFormController::class, 'store'])->name('submit.form');

    // Fetch users for form selection
    Route::post('/fetch-users', [CommunicationFormController::class, 'fetchUsers'])->name('fetch.users');

    // View received documents
    Route::get('/received-documents', [CommunicationFormController::class, 'receivedDocuments'])->name('received.documents');

    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin controls
    Route::get('/admin_controls', [AdminControlsController::class, 'admin_controls'])->name('admin_controls');
    // User management
    Route::post('/admin_controls/users', [AdminControlsController::class, 'store']);
    Route::delete('/admin_controls/users/{user}', [AdminControlsController::class, 'destroy']);
});