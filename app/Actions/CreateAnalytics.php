<?php

namespace App\Actions;

use App\Models\Analytics;
use App\Models\Application;
use Illuminate\Http\Request;

class CreateAnalytics
{
    public function create(Request $request, Application $application): void
    {
        $data = $request->ip().'|'.$request->userAgent();

        $sessionId = hash_hmac('sha256', $data, config('app.key'));

        $existing = $application->analytics()
            ->where('session', $sessionId)
            ->first();

        if ($existing instanceof Analytics) {
            $existing->increment('count');

            return;
        }

        $application->analytics()->create([
            'session' => $sessionId,
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => $request->query(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'user_agent' => $request->userAgent(),
            'count' => 1,
        ]);
    }
}
