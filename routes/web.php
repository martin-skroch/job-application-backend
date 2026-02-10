<?php

use App\Http\Controllers\RedirectController;
use Livewire\Volt\Volt;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->away(config('app.frontend_url'));
    // return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Volt::route('applications', 'applications.index')->name('applications.index');
    Volt::route('applications/{application}', 'applications.show')->name('applications.show');

    Volt::route('profiles', 'profiles.index')->name('profiles.index');
    Volt::route('profiles/{profile}', 'profiles.show')->name('profiles.show');
    Volt::route('profiles/{profile}/experiences', 'experiences.index')->name('profiles.experiences');
    Volt::route('profiles/{profile}/educations', 'experiences.index')->name('profiles.educations');
    Volt::route('profiles/{profile}/skills', 'skills.index')->name('profiles.skills');
    Volt::route('profiles/{profile}/impressions', 'impressions.index')->name('profiles.impressions');
});

Route::middleware(['auth'])->group(function () {
    Route::get('redirect', RedirectController::class)->name('redirect');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');
    Volt::route('settings/tokens', 'settings.tokens')->name('tokens.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')->middleware(
        when(
            Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
            ['password.confirm'],
            [],
        ),
    )->name('two-factor.show');
});
