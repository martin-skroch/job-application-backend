<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
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
