<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApplicationsController;

Route::group(['as' => 'api.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('application/{application}', [ApplicationsController::class, 'fetch'])->name('application');
    // Route::post('application/request', [ApplicationsController::class, 'request'])->name('application.request');
});
