<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SkillsController;
use App\Http\Controllers\Api\ProfilesController;
use App\Http\Controllers\Api\ExperiencesController;
use App\Http\Controllers\Api\ApplicationsController;

Route::group(['as' => 'api.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('application/{application}', ApplicationsController::class)->name('application');
});
