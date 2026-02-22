<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('applications', 'pages::applications.index')->name('applications.index');
    Route::livewire('applications/{application}', 'pages::applications.show')->name('applications.show');
});
