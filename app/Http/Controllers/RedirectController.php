<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        if (!$request->filled('url')) {
            abort(404);
        }

        $validated = $request->validate([
            'url' => ['bail', 'required', 'url:http,https'],
        ]);

        return redirect()->away($validated['url']);
    }
}
