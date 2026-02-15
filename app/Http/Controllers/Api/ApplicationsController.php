<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateAnalytics;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Notifications\ApplicationRequestedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class ApplicationsController extends Controller
{
    public function __construct(
        private readonly CreateAnalytics $createAnalytics
    ) {}

    public function fetch(Request $request, Application $application): JsonResource|JsonResponse
    {
        if (!$application->isPublic()) {
            return new JsonResponse('Not found', 404);
        }

        if (App::environment('local')) {
            sleep(1);
        }

        return new ApplicationResource($application);
    }

    public function request(Request $request)
    {
        $validated = $request->validate([
            'company' =>  ['required', 'string', 'max:255'],
            'name' =>  ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $application = $request->user()->applications()->create([
            'source' => $request->headers->get('referer'),
            'company_name' => $validated['company'],
            'contact_name' => $validated['name'],
            'contact_email' => $validated['email'],
        ]);

        $this->createAnalytics->create(
            $request,
            $application
        );

        $request->user()->notify(new ApplicationRequestedNotification($application));

        return new ApplicationResource($application);
    }
}
