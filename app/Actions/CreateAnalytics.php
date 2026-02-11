<?php

namespace App\Actions;

use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CreateAnalytics
{
    public function create(Request $request, Application $application): void
    {
        $data = $request->ip() . '|' . $request->userAgent();

        $sessionId = hash_hmac('sha256', $data, config('app.key'));

        $application->analytics()->create([
            'session' => $sessionId,
            'method' => $request->method(),
            'path' => $request->path(),
            'query' => $request->query(),
            'headers' => $request->headers->all(),
            'payload' => $request->all(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
