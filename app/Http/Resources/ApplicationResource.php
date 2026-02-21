<?php

namespace App\Http\Resources;

use App\Enum\ExperienceType;
use App\Enum\SalaryBehaviors;
use App\Http\Resources\ExperienceResource;
use App\Http\Resources\ImpressionResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\SkillResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Util\HtmlFilter;

class ApplicationResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->whenNotNull($this->profile);

        $experiences = $this->whenNotNull($this->profile?->experiences(ExperienceType::Work)->get());
        $educations = $this->whenNotNull($this->profile?->experiences(ExperienceType::Education)->get());
        $training = $this->whenNotNull($this->profile?->experiences(ExperienceType::Training)->get());
        $school = $this->whenNotNull($this->profile?->experiences(ExperienceType::School)->get());

        $skills = $this->whenNotNull($this->profile?->skills);
        $impressions = $this->whenNotNull($this->profile?->impressions);

        $salaryDesire = match($this->salary_behavior) {
            SalaryBehaviors::Inherit => is_scalar($this->profile->salary_desire) ? Number::currency($this->profile->salary_desire, precision: 0) : null,
            SalaryBehaviors::Override => is_scalar($this->salary_desire) ? Number::currency($this->salary_desire, precision: 0) : null,
            SalaryBehaviors::Hidden => null,
        };

        return [
            'id' => $this->id,
            'title' => $this->whenHas('title'),
            'salary_desire' => $salaryDesire,
            'greeting' => $this->markdown($this->greeting),
            'text' => $this->markdown($this->text),
            'company' => $this->whenHas('company_name'),
            'contact' => $this->whenHas('contact_name'),
            'profile' => new ProfileResource($profile),
            'experiences' => ExperienceResource::collection($experiences),
            'educations' => ExperienceResource::collection($educations),
            'training' => ExperienceResource::collection($training),
            'school' => ExperienceResource::collection($school),
            'skills' => SkillResource::collection($skills),
            'impressions' => ImpressionResource::collection($impressions),
        ];
    }

    private function markdown(?string $input = null): string|null
    {
        $converter = new CommonMarkConverter([
            'html_input' => HtmlFilter::ALLOW,
            'allow_unsafe_links' => false,
        ]);

        if (filled($input)) {
            return $converter->convert($input);
        }

        return null;
    }
}
