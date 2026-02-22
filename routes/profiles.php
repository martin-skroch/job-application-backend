<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('profiles', 'pages::profiles.index')->name('profiles.index');
    Route::livewire('profiles/{profile}', 'pages::profiles.show')->name('profiles.show');
    Route::livewire('profiles/{profile}/experiences', 'pages::experiences.index')->name('profiles.experiences');
    Route::livewire('profiles/{profile}/educations', 'pages::experiences.index')->name('profiles.educations');
    Route::livewire('profiles/{profile}/training', 'pages::experiences.index')->name('profiles.training');
    Route::livewire('profiles/{profile}/school', 'pages::experiences.index')->name('profiles.school');
    Route::livewire('profiles/{profile}/skills', 'pages::skills.index')->name('profiles.skills');
    Route::livewire('profiles/{profile}/impressions', 'pages::impressions.index')->name('profiles.impressions');
});
