<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('register', function () {
    return redirect()->route('login');
})->name('register');

Route::post('register', function () {
    return redirect()->route('login');
})->name('register.store');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('privacy', function () {
    return Inertia::render('Privacy');
})->name('privacy');

require __DIR__.'/settings.php';
