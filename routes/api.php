<?php

use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\ResumeResource;

Route::get('resume/{resume}', function(Request $request, Resume $resume): JsonResponse|ResumeResource {

    $token = $request->bearerToken();

    if (!$resume || $resume->token !== $token) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    return new ResumeResource($resume);

})->name('api.resume');
