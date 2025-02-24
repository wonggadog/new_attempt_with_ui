<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommunicationFormController;

// Display the form
Route::get('/', [CommunicationFormController::class, 'index']);

// Handle form submission
Route::post('/submit-form', [CommunicationFormController::class, 'store'])->name('submit.form');