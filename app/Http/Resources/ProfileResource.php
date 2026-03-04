<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProfileResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = null;
        $phone = null;
        $email = null;

        $birthdate = null;
        $age = null;

        if (filled($this->image) && Storage::exists($this->image)) {
            $image = Storage::url($this->image);
        }

        if (filled($this->phone)) {
            $phone = base64_encode('tel:'.$this->phone);
        }

        if (filled($this->email)) {
            $email = base64_encode('mailto:'.$this->email);
        }

        if ($this->birthdate instanceof Carbon) {
            $birthdate = $this->birthdate->format('Y-m-d');
            $age = $this->birthdate->age;
        }

        return [
            'image' => $image,
            'name' => $this->name,
            'address' => $this->address,
            'post_code' => $this->post_code,
            'location' => $this->location,
            'birthdate' => $birthdate,
            'birthplace' => $this->birthplace,
            'age' => $age,
            'phone' => $phone,
            'email' => $email,
            'website' => $this->website,
        ];
    }
}
