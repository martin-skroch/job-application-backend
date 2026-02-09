<?php

use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Models\Experience;
use App\Models\Profile;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;

new class extends Component {
    public Profile $profile;

    public bool $isEditing = false;

    public string $entry = '';
    public ?string $exit = null;
    public ?string $institution = '';
    public string $position = '';
    public ?string $location = '';
    public ?string $office;
    public ?string $type = '';
    public array $skills = [];
    public ?string $description = '';
    public bool $active = false;

    public ?string $experienceId = null;
    public ?Collection $profileSkills = null;
    public ?Collection $experienceSkills = null;

    public function mount(): void
    {
        $this->authorize('viewAny', $this->profile);
    }

    public function open(?string $id = null): void
    {
        $this->resetForm();

        $this->isEditing = Str::isUlid($id);

        if ($this->isEditing) {
            $experience = Experience::findOrFail($id);

            $this->entry = $experience->entry?->format('Y-m-d');
            $this->exit = $experience->exit?->format('Y-m-d');
            $this->institution = $experience->institution;
            $this->position = $experience->position;
            $this->location = $experience->location;
            $this->office = $experience->office;
            $this->type = $experience->type;
            $this->skills = $experience->skills->pluck('id')->all();
            $this->description = $experience->description;
            $this->active = $experience->active;
            $this->experienceId = $experience->id;

            $this->experienceSkills = $experience->skills;
        }

        Flux::modal('experience-modal')->show();
    }

    public function save(): void
    {
        $hasId = $this->experienceId !== null && Str::isUlid($this->experienceId);
        $request = $hasId ? new UpdateExperienceRequest() : new StoreExperienceRequest();
        $experiences = $this->profile->experiences();

        $validated = $this->validate($request->rules($this->profile));

        $skills = collect($validated['skills'])->flip()->map(function(string $skill) {
            return ['order' => (int) $skill + 1];
        })->all();

        unset($validated['skills']);

        if ($hasId) {
            $experience = $experiences->where('id', $this->experienceId)->firstOrFail();
            $experience->update($validated);
        } else {
            $experience = $experiences->create($validated);
        }

        $skills = $experience->skills()->sync($skills);

        Flux::modal('experience-modal')->close();

        $this->resetForm();
    }

    public function delete(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $experience = $this->profile->experiences()->find($id);

        if (!$experience instanceof Experience) {
            return;
        }

        $experience->delete();
    }

    public function clearExit(): void
    {
        $this->exit = null;
    }

    public function toggleActive(string $id, bool $active = false): void
    {
        $experience = Experience::where('id', $id);
        $experience->update(['active' => !$active]);
    }

    public function resetForm(): void
    {
        $this->reset([
            'entry',
            'exit',
            'isEditing',
            'institution',
            'position',
            'location',
            'office',
            'type',
            'skills',
            'description',
            'active',
            'experienceId',
        ]);

        $this->resetErrorBag();
    }

    public function chooseSkill(): void
    {
        $this->profileSkills = $this->profile->skills;

        Flux::modal('experience-skills-modal')->show();
    }

    public function addSkill(): void
    {

    }

    public function removeSkill(string $experienceId, string $skillId): void
    {
        $experience = Experience::where('id', $experienceId)->first();
        $experience->skills()->detach($skillId);
    }

    public function reorderSkills(string $experienceId, array $items): void
    {
        $experience = Experience::findOrFail($experienceId ?? $this->experienceId);

        foreach ($items as $item) {
            $experience->skills()->updateExistingPivot($item['id'], [
                'order' => $item['order']
            ]);
        }

        $this->experienceSkills = $experience->skills;
    }

    public function resetSkills(): void
    {
        $this->experienceId = null;
        $this->experienceSkills = null;
    }
}; ?>

<section class="space-y-6">
    <x-profiles.layout :profile="$profile" :heading="__('Experiences')" :subheading="__('Manage your experiences.')">
        <x-slot:actions>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Add Experience') }}
            </flux:button>
        </x-slot>

        <div class="space-y-6">
            @foreach ($profile->experiences as $experience)
            <flux:callout class="group{{ !$experience->active ? ' opacity-60 inactive' : '' }}" inline>

                <div class="text-2xl font-medium font-mono">{{ $experience->from_to }}</div>

                <div class="text-2xl font-medium">{{ $experience->institution }}</div>

                <div class="flex items-center gap-4">
                    @if ($experience->duration)
                    <div class="flex items-center gap-1 text-sm">
                        <flux:icon name="calendar-days" class="size-4" /> {{ $experience->duration }}
                    </div>
                    @endif

                    @if ($experience->location)
                    <div class="flex items-center gap-1 text-sm">
                        <flux:icon name="map-pin" class="size-4" /> {{ $experience->location }}
                    </div>
                    @endif

                    @if ($experience->office)
                    <div class="flex items-center gap-1 text-sm">
                        <flux:icon name="computer-desktop" class="size-4" /> {{ $experience->office }}
                    </div>
                    @endif
                </div>

                <div class="font-medium">{{ $experience->position }}</div>

                @if ($experience->description)
                <p class="text-zinc-400">{{ $experience->description }}</p>
                @endif

                @if ($experience->skills->count() > 0)
                <div class="flex flex-wrap gap-2" x-sort x-on:sort.stop="$wire.reorderSkills('{{ $experience->id }}', Array.from($el.children).map((el, index) => ({id: el.dataset.id, order: index + 1})))">
                    @foreach ($experience->skills as $skill)
                    <flux:badge size="sm" class="gap-1" :data-id="$skill->id" x-sort:item>
                        {{ $skill->name }}
                        <button class="shrink-0 rounded-full hover:bg-red-800" wire:click="removeSkill('{{ $experience->id }}', '{{ $skill->id }}')">
                            <flux:icon name="x-mark" class="size-4 p-0.5" />
                        </button>
                    </flux:badge>
                    @endforeach

                    <flux:badge size="sm" wire:click="chooseSkill('{{ $experience->id }}')">
                        <flux:icon name="plus" />
                    </flux:badge>
                </div>
                @endif

                <x-slot name="actions">
                    <flux:dropdown>
                        <flux:button icon="ellipsis-horizontal" variant="ghost" />

                        <flux:menu>
                            <flux:menu.item icon="pencil-square" wire:click="open('{{ $experience->id }}')">
                                {{ __('Edit') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item icon="{{ $experience->active ? 'eye-slash' : 'eye' }}" wire:click="toggleActive('{{ $experience->id }}', {{ $experience->active }})">
                                {{ $experience->active ? __('Deactivate') : __('Activate') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item variant="danger" icon="trash" wire:click="delete('{{ $experience->id }}')" wire:confirm="{{ __('Are you sure you want to delete this impression?') }}">
                                {{ __('Delete') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </x-slot>

            </flux:callout>
            @endforeach
        </div>
    </x-profiles.layout>

    <x-flyout name="experience-modal" wire:close="resetForm">
        <flux:heading size="xl" level="1">{{ $isEditing ? __('Edit Experience') : __('Add Experience') }}</flux:heading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="save">
            <div class="grid 2xl:grid-cols-2 items-start gap-6">
                <flux:input wire:model="entry" :label="__('Entry')" type="date" required />

                <flux:field>
                    <flux:label>{{ __('Exit') }}</flux:label>

                    <flux:input.group>
                        <flux:input wire:model="exit" type="date" />
                        @if ($exit)
                            <flux:button icon="x-mark" wire:click="clearExit"></flux:button>
                        @endif
                    </flux:input.group>

                    <flux:error name="exit" />
                </flux:field>
            </div>

            <div class="grid 2xl:grid-cols-2 items-start gap-6">
                <flux:input wire:model="institution" :label="__('Institution')" type="text" required />
                <flux:input wire:model="position" :label="__('Position')" type="text" />
            </div>

            <div class="grid 2xl:grid-cols-2 items-start gap-6">
                <flux:input wire:model="location" :label="__('Location')" type="text" />
                <flux:input wire:model="office" :label="__('Office')" type="text" />
            </div>

            <flux:input wire:model="type" :label="__('Type')" type="text" />

            <flux:checkbox.group wire:model.live="skills" class="grid grid-cols-3" :label="__('Skills')" >
                @foreach ($profile->skills as $skill)
                <flux:checkbox :label="$skill->name" :value="$skill->id" />
                @endforeach
            </flux:checkbox.group>

            <flux:textarea wire:model="description" :label="__('Description')" resize="vertical" />

            <flux:switch wire:model="active" :label="__('Active')" align="left" />

            <div class="inline-flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ $isEditing ? 'Save' : __('Add') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>

    <x-flyout name="experience-skills-modal">
        <div class="flex flex-col gap-px">
            @foreach ($profileSkills?->all() ?? [] as $skill)
            <flux:callout class="border-0 not-first:rounded-t-none not-last:rounded-b-none p-0!" inline>
                <div class="flex items-center gap-2 text-sm">
                    <span class="font-medium">{{ $skill->name }}</span>

                    @if($skill->info)
                        <small class="text-zinc-500">({{ $skill->info }})</small>
                    @endif
                </div>
            </flux:callout>
            @endforeach
        </div>

        <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">
            {{ __('Cancel') }}
        </flux:button>
    </x-flyout>
</section>
