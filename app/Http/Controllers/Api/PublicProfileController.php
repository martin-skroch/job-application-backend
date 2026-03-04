<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicProfileResource;
use App\Models\Profile;
use Illuminate\Support\Facades\App;

class PublicProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Profile $profile)
    {
        if (App::environment('local')) {
            sleep(1);
        }

        $resource = $profile->load('skills');

        return new PublicProfileResource($resource);
    }
}
