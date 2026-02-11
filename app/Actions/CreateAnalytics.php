<?php

namespace App\Actions;

use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CreateAnalytics
{
    public function create(
        Request $request,
        Application $application
    ): void {
        $cookieName = 'analytics_session';
        $cookieValue = $request->cookie($cookieName, (string) Str::uuid());

        $application->analytics()->create([
            'session' => $cookieValue,
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => $request->query(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (!$request->hasCookie($cookieName)) {
            response()->headers->setCookie(cookie()->forever($cookieName, $cookieValue));
        }
    }
}
