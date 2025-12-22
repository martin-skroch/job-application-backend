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
        return new ResumeResource($resume->loadCount('experiences', 'skills'));
    }
}
