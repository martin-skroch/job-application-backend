<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;
use function is_string;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->image;
        $phone = $this->phone;
        $email = $this->email;

        $birthdate = null;
        $age = null;

        if ($image !== null && Storage::exists($image)) {
            $image = Storage::url($image);
        }

        if (is_string($phone) && $phone !== '') {
            $phone = base64_encode('tel:' . $phone);
        }

        if (is_string($email) && $email !== '') {
            $email = base64_encode('mailto:' . $email);
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
