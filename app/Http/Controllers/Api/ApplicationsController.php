<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class ApplicationsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Application $application): JsonResource|JsonResponse
    {
        if (!$application->sent_at instanceof Carbon) {
            return new JsonResponse('Not found', 404);
        }

        if (App::environment('local')) {
            sleep(1);
        }

        return new ApplicationResource($application);
    }
}
