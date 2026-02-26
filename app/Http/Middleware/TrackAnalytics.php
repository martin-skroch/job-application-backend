<?php

namespace App\Http\Middleware;

use App\Actions\CreateAnalytics;
use App\Models\Application;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalytics
{
    public function __construct(
        private readonly CreateAnalytics $createAnalytics
    ) {}

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        $application = $request->route('application');

        if (! $application instanceof Application) {
            return $response;
        }

        $this->createAnalytics->create(
            $request,
            $application
        );

        return $response;
    }
}
