<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateAnalytics;
use App\Actions\CreateApplicationFromVacancy;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\VacancyResource;
use App\Models\Application;
use App\Notifications\ApplicationRequestedNotification;
use App\Notifications\VacancyRequestedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ApplicationsController extends Controller
{
    public function __construct(
        private readonly CreateAnalytics $createAnalytics,
        private readonly CreateApplicationFromVacancy $createApplicationFromVacancy,
    ) {}

    public function fetch(Request $request, Application $application): ApplicationResource|JsonResponse
    {

        if (! $application->isPublic()) {
            return new JsonResponse('Not found', 404);
        }

        if ($application->isArchived()) {
            return new JsonResponse('Gone', 410);
        }

        if (App::environment('local')) {
            sleep(1);
        }

        return new ApplicationResource($application);
    }

    public function request(Request $request): ApplicationResource|VacancyResource
    {
        $publicId = $request->string('id');
        $query = Application::withTrashed()->where('public_id', $publicId);

        if ($request->filled('id') && $query->exists()) {
            $application = $query->first();

            $this->createAnalytics->create($request, $application);

            $request->user()->notify(new ApplicationRequestedNotification($application));

            return new ApplicationResource($application);
        } else {
            $validated = $request->validate([
                'company' => ['string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
            ]);

            $validated['company'] = $validated['name'];

            $vacancy = $request->user()->vacancies()->create([
                'source' => $request->headers->get('referer'),
                'company_name' => $validated['company'],
                'contact_name' => $validated['name'],
                'contact_email' => $validated['email'],
            ]);

            $request->user()->notify(new VacancyRequestedNotification($vacancy));

            return new VacancyResource($vacancy);
        }
    }
}
