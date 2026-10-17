<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('vacancies', 'pages::vacancies.index')->name('vacancies.index');
    Route::livewire('vacancies/{vacancy}', 'pages::vacancies.show')->name('vacancies.show');
});
