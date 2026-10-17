<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVacancyRequest;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;

class VacanciesController extends Controller
{
    /**
     * Bookmark a vacancy. An existing listing with the same source is returned instead of creating a duplicate.
     */
    public function store(StoreVacancyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $vacancies = $request->user()->vacancies()->withTrashed();

        $existingVacancy = filled($validated['source'] ?? null)
            ? $vacancies->where('source', $validated['source'])->first()
            : null;

        if ($existingVacancy instanceof Vacancy) {
            return (new VacancyResource($existingVacancy))->response()->setStatusCode(200);
        }

        $vacancy = $request->user()->vacancies()->create($validated);

        return (new VacancyResource($vacancy))->response()->setStatusCode(201);
    }
}
