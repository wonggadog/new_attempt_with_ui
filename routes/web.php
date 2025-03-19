<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommunicationFormController;
use Illuminate\Support\Facades\Auth;

// Authentication Routes
Auth::routes();

// Guest routes
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
});

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Display the form (your existing functionality)
    Route::get('/', [CommunicationFormController::class, 'index'])->name('home');
    
    // Handle form submission
    Route::post('/submit-form', [CommunicationFormController::class, 'store'])->name('submit.form');
    
    // Fetch users
    Route::post('/fetch-users', [CommunicationFormController::class, 'fetchUsers'])->name('fetch.users');
    
    // Logout route
    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});