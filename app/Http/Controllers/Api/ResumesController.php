<?php

namespace App\Http\Controllers\Api;

use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ResumeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Resume $resume): JsonResource|JsonResponse
    {
        if (!$resume->api_active) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $apiToken = $request->bearerToken();

        if (!$resume || $resume->api_token !== $apiToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $resource = $resume->loadCount('experiences', 'skills');

        return new ResumeResource($resource);
    }
}
