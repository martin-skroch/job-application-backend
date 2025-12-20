<?php

use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\ResumeResource;

Route::get('resume/{resume}', function(Request $request, Resume $resume): JsonResponse|ResumeResource {
    if (!$resume->api_active) {
        return response()->json(['message' => 'Not found'], 404);
    }

    $apiToken = $request->bearerToken();

    if (!$resume || $resume->api_token !== $apiToken) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    return new ResumeResource($resume);

})->name('api.resume');
