<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index'); // Ensure your file is named index.blade.php inside resources/views/
});