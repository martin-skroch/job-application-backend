<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->redirectToRoute('login');

    return view('welcome');
})->name('home');

Route::get('file/{file}', FileController::class)->name('file');

Route::middleware(['auth'])->group(function () {
    Route::get('redirect', RedirectController::class)->name('redirect');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/applications.php';
require __DIR__.'/profiles.php';
require __DIR__.'/settings.php';
