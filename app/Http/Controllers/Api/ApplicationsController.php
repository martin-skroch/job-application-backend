<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Actions\CreateAnalytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationsController extends Controller
{
    public function __construct(
        private readonly CreateAnalytics $createAnalytics
    ) {}

    public function fetch(Request $request, Application $application): JsonResource|JsonResponse
    {
        if (!$application->sent_at instanceof Carbon) {
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
            'phone' => ['nullable', 'string'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $application = $request->user()->applications()->create([
            'company_name' => $validated['company'],
            'contact_name' => $validated['name'],
            'contact_email' => $validated['email'],
            'contact_phone' => $validated['phone'],
            'company_website' => $validated['website'],
            'notes' => $validated['message'],
        ]);

        $this->createAnalytics->create(
            $request,
            $application,
        );

        return new ApplicationResource($application);
    }
}
