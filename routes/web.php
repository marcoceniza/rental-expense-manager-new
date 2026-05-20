<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/login', function () {
    return Inertia::render('auth/Login');
})->name('login');

Route::get('/rental/build/{path}', function ($path) {
    return redirect('/build/' . $path);
})->where('path', '.*');

Route::get('/rental/build', function () {
    return redirect('/build');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/transactions.php';
