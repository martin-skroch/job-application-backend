<?php

namespace App\Providers;

use App\Models\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        app()->singleton('markdown', function () {
            return new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        });
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

        Blade::directive('markdown', function($expression) {
            return "<?php echo app('markdown')->convert($expression); ?>";
        });
    }
}
