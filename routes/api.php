<?php

use App\Http\Controllers\Api\ApplicationsController;
use App\Http\Controllers\Api\PublicProfileController;
use App\Http\Controllers\Api\VacanciesController;
use App\Http\Middleware\TrackAnalytics;
use Illuminate\Support\Facades\Route;

Route::group(['as' => 'api.', 'middleware' => ['auth:sanctum', TrackAnalytics::class]], function () {
    Route::post('application/request', [ApplicationsController::class, 'request'])->name('application.request');
    Route::get('application/{application}', [ApplicationsController::class, 'fetch'])->name('application');
    Route::get('profile/{profile}', PublicProfileController::class)->name('profile');
});

Route::group(['as' => 'api.', 'middleware' => ['auth:sanctum']], function () {
    Route::post('vacancies', [VacanciesController::class, 'store'])->name('vacancies.store');
});
