<?php

use App\Models\Experience;
use App\Models\Skill;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public ?string $experienceId = null;
    public ?Collection $experienceSkills = null;
    public ?Collection $availableSkills = null;

    #[On('manage-experience-skills')]
    public function open(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $this->experienceId = $id;
        $this->loadSkills();

        Flux::modal('experience-skills-modal')->show();
    }

    public function addSkill(string $skillId): void
    {
        if (!Str::isUlid($skillId)) {
            return;
        }

        $experience = Experience::findOrFail($this->experienceId);
        $nextOrder = $experience->skills()->count() + 1;

        $experience->skills()->attach($skillId, ['order' => $nextOrder]);

        $this->loadSkills();
    }

    public function removeSkill(string $skillId): void
    {
        if (!Str::isUlid($skillId)) {
            return;
        }

        Experience::findOrFail($this->experienceId)->skills()->detach($skillId);

        $this->loadSkills();
    }

    public function reorderSkills(array $items): void
    {
        $experience = Experience::findOrFail($this->experienceId);

        foreach ($items as $item) {
            $experience->skills()->updateExistingPivot($item['id'], [
                'order' => $item['order'],
            ]);
        }

        $this->experienceSkills = $experience->skills;
    }

    private function loadSkills(): void
    {
        $experience = Experience::with('profile')->findOrFail($this->experienceId);

        $this->experienceSkills = $experience->skills;

        $attachedIds = $this->experienceSkills->pluck('id')->all();

        $this->availableSkills = Skill::where('profile_id', $experience->profile_id)
            ->whereNotIn('id', $attachedIds)
            ->get();
    }
};
?>

<div>
    <x-flyout name="experience-skills-modal">
        <flux:heading size="xl" level="1">{{ __('Manage skills') }}</flux:heading>

        <flux:separator variant="subtle" />

        @if ($experienceSkills?->isNotEmpty())
        <div>
            <flux:heading size="sm" class="mb-3">{{ __('Current skills') }}</flux:heading>

            <div class="flex flex-col gap-px" x-sort x-on:sort.stop="$wire.reorderSkills(Array.from($el.children).map((e, i) => ({id: e.dataset.id, order: i + 1})))">
                @foreach ($experienceSkills as $skill)
                <flux:callout class="border-0 not-first:rounded-t-none not-last:rounded-b-none cursor-grab" wire:key="skill-{{ $skill->id }}" data-id="{{ $skill->id }}" x-sort:item inline>
                    <x-slot name="icon" class="items-center!">
                        <flux:icon name="bars-3" class="size-4" />
                    </x-slot>

                    <flux:callout.heading>
                        {{ $skill->name }}

                        @if ($skill->info)
                            <small class="text-zinc-500">({{ $skill->info }})</small>
                        @endif
                    </flux:callout.heading>

                    <x-slot name="actions">
                        <flux:button variant="ghost" icon="x-mark" wire:click="removeSkill('{{ $skill->id }}')" />
                    </x-slot>
                </flux:callout>
                @endforeach
            </div>
        </div>
        @endif

        @if ($availableSkills?->isNotEmpty())
        <div>
            <flux:heading size="sm" class="mb-3">{{ __('Available skills') }}</flux:heading>

            <div class="flex flex-col gap-px">
                @foreach ($availableSkills as $skill)
                <flux:callout class="border-0 not-first:rounded-t-none not-last:rounded-b-none" inline wire:key="available-{{ $skill->id }}">
                    <flux:callout.heading>
                        {{ $skill->name }}

                        @if ($skill->info)
                            <small class="text-zinc-500">({{ $skill->info }})</small>
                        @endif
                    </flux:callout.heading>

                    <x-slot name="actions">
                        <flux:button variant="ghost" icon="plus" wire:click="addSkill('{{ $skill->id }}')" />
                    </x-slot>
                </flux:callout>
                @endforeach
            </div>
        </div>
        @endif

        @if ($experienceSkills?->isEmpty() && $availableSkills?->isEmpty())
        <flux:callout>{{ __('No skills available for this profile.') }}</flux:callout>
        @endif
    </x-flyout>
</div>
