<?php

namespace App\Providers;

use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::macro('withTimezone', function(): Carbon|string {
            return $this->tz(Auth::user()?->timezone ?? config('app.timezone'));
        });

        Route::bind('application', function (string $value) {
            if (Str::isUlid($value)) {
                $application = Application::where('id', $value);
            } else {
                $application = Application::where('public_id', $value);;
            }

            return $application->firstOrFail();
        });
    }
}
