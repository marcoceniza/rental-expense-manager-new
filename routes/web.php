<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/login', function () {
    return Inertia::render('auth/Login');
})->name('login');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/transactions.php';
