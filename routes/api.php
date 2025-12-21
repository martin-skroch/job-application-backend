<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ResumesController;
use App\Http\Controllers\Api\ExperiencesController;
use App\Http\Controllers\Api\SkillsController;

Route::group(['as' => 'api.'], function () {
    Route::get('resume/{resume}', ResumesController::class)->name('resume');
    Route::get('resume/{resume}/experiences', ExperiencesController::class)->name('resume.experiences');
    Route::get('resume/{resume}/skills', SkillsController::class)->name('resume.skills');
});
