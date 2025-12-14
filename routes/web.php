<?php

use App\Models\Resume;
use Livewire\Volt\Volt;
use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\ResumeResource;
use Illuminate\Support\Facades\Redirect;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Volt::route('resumes', 'resumes.index')->name('resumes.index');
    Volt::route('resumes/create', 'resumes.create')->name('resumes.create');
    Volt::route('resumes/{resume}', 'resumes.show')->name('resumes.show');
    Volt::route('resumes/{resume}/edit', 'resumes.edit')->name('resumes.edit');

    Volt::route('resumes/{resume}/experiences', 'experiences.index')->name('resumes.experiences');
    Volt::route('resumes/{resume}/skills', 'skills.index')->name('resumes.skills');
    Volt::route('resumes/{resume}/certificates', 'resumes.certificates.index')->name('resumes.certificates');
    Volt::route('resumes/{resume}/languages', 'resumes.languages.index')->name('resumes.languages');

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

Route::get('r/{resume}', function(Request $request, Resume $resume): JsonResponse|ResumeResource {

    $token = $request->bearerToken();

    if (!$resume || $resume->token !== $token) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    return new ResumeResource($resume);

})->name('resume');

Route::get('redirect', function(Request $request): RedirectResponse {

    if (!$request->has('url')) {
        abort(404);
    }

    return redirect()->away($request->string('url'));

})->name('redirect');
