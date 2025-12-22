<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SkillsController;
use App\Http\Controllers\Api\ResumesController;
use App\Http\Controllers\Api\ExperiencesController;

Route::group(['as' => 'api.', 'middleware' => ['auth:sanctum']], function () {
    // Route::get('user', fn (Request $request) => $request->user());
    Route::get('resume/{resume}', ResumesController::class)->name('resume');
    Route::get('resume/{resume}/experiences', ExperiencesController::class)->name('resume.experiences');
    Route::get('resume/{resume}/skills', SkillsController::class)->name('resume.skills');
});
