<?php

namespace App\Http\Controllers\Api;

use App\Models\Resume;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExperienceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class ExperiencesController extends Controller
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

        $resource = $resume->experiences;

        return ExperienceResource::collection($resource);
    }
}
