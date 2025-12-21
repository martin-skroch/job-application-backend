<?php

use Livewire\Volt\Volt;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Volt::route('resumes', 'resumes.index')->name('resumes.index');
    Volt::route('resumes/{resume}', 'resumes.show')->name('resumes.show');
    Volt::route('resumes/{resume}/experiences', 'experiences.index')->name('resumes.experiences');
    Volt::route('resumes/{resume}/skills', 'skills.index')->name('resumes.skills');
    Volt::route('resumes/{resume}/settings', 'resumes.settings')->name('resumes.settings');

    Volt::route('vacancies', 'vacancies.index')->name('vacancies.index');
    Volt::route('vacancies/create', 'vacancies.create')->name('vacancies.create');
    Volt::route('vacancies/{vacancy}', 'vacancies.show')->name('vacancies.show');
    Volt::route('vacancies/{vacancy}/edit', 'vacancies.edit')->name('vacancies.edit');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::get('redirect', function(Request $request): RedirectResponse {

    if (!$request->has('url')) {
        abort(404);
    }

    return redirect()->away($request->string('url'));

})->name('redirect');
