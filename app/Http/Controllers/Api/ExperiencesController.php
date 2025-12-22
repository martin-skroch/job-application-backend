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
        return ExperienceResource::collection($resume->experiences);
    }
}
