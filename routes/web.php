<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommunicationFormController;
use Illuminate\Support\Facades\Auth;

// Authentication Routes
Auth::routes();

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Display the form (your existing functionality)
    Route::get('/', [CommunicationFormController::class, 'index'])->name('home');
    
    // Handle form submission
    Route::post('/submit-form', [CommunicationFormController::class, 'store'])->name('submit.form');
    
    // Fetch users
    Route::post('/fetch-users', [CommunicationFormController::class, 'fetchUsers'])->name('fetch.users');
});

// Redirect unauthenticated users to login
Route::get('/login', function () {
    return redirect()->route('login');
})->name('login.redirect');
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
