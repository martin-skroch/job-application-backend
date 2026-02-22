<?php

use App\Enum\ExperienceType;
use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component {
    public Profile $profile;
    public ?Collection $experiences = null;

    public bool $isEditing = false;

    public string $routeName;
    public string $entry = '';
    public ?string $exit = null;
    public ?string $institution = '';
    public string $position = '';
    public ?string $location = '';
    public ?string $office;
    public ?ExperienceType $type = null;
    public array $skills = [];
    public ?string $description = '';
    public bool $active = false;

    public ?string $experienceId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', $this->profile);

        $this->routeName = Route::currentRouteName();

        $this->type = match(true) {
            $this->routeName === 'profiles.experiences' => ExperienceType::Work,
            $this->routeName === 'profiles.educations' => ExperienceType::Education,
            $this->routeName === 'profiles.training' => ExperienceType::Training,
            $this->routeName === 'profiles.school' => ExperienceType::School,
        };

        if ($this->type instanceof ExperienceType) {
            $this->experiences = $this->profile->experiences($this->type)->get();
        } else {
            $this->experiences = new Collection();
        }
    }

    public function open(?string $id = null): void
    {
        $this->resetForm();

        $this->isEditing = Str::isUlid($id);

        if ($this->isEditing) {
            $experience = $this->profile->experiences($this->type)->findOrFail($id);

            $this->entry = $experience->entry?->format('Y-m-d');
            $this->exit = $experience->exit?->format('Y-m-d');
            $this->institution = $experience->institution;
            $this->position = $experience->position;
            $this->location = $experience->location;
            $this->office = $experience->office;
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

        $experience = Experience::where([
            'id' => $id,
            'user_id' => Auth::user()->id,
            'profile_id' => $this->profile->id,
        ]);

        $experience->delete();
    }

    public function clearExit(): void
    {
        $this->exit = null;
    }

    public function toggleActive(string $id, bool $active = false): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $experience = Experience::where([
            'id' => $id,
            'profile_id' => $this->profile->id,
        ]);

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
            'skills',
            'description',
            'active',
            'experienceId',
        ]);

        $this->resetErrorBag();
    }

    public function manageFiles(string $id): void
    {
        $this->dispatch('manage-experience-files', id: $id);
    }

    public function manageSkills(string $id): void
    {
        $this->dispatch('manage-experience-skills', id: $id);
    }
}; ?>

<section class="space-y-6">
    <x-pages::profiles.layout :profile="$profile" :heading="__('Experiences')" :subheading="__('Manage your experiences.')">
        <x-slot:actions>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Add Experience') }}
            </flux:button>
        </x-slot>

        <div class="space-y-6">
            @foreach ($experiences as $experience)
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
                <div class="flex flex-wrap gap-2">
                    @foreach ($experience->skills as $skill)
                    <flux:badge size="sm">{{ $skill->name }}</flux:badge>
                    @endforeach
                </div>
                @endif

                <div>
                    {{ __('Files') }}: {{ $experience->files()->count() }}
                </div>

                <x-slot name="actions">
                    <flux:dropdown>
                        <flux:button icon="ellipsis-horizontal" variant="ghost" />

                        <flux:menu>
                            <flux:menu.item icon="pencil-square" wire:click="open('{{ $experience->id }}')">
                                {{ __('Edit Experience') }}
                            </flux:menu.item>

                            <flux:menu.item icon="paper-clip" wire:click="manageFiles('{{ $experience->id }}')">
                                {{ __('Manage files') }}
                            </flux:menu.item>

                            <flux:menu.item icon="academic-cap" wire:click="manageSkills('{{ $experience->id }}')">
                                {{ __('Manage skills') }}
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
    </x-pages::profiles.layout>

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

            <flux:radio.group wire:model="type" :label="__('Type')" variant="segmented">
                @foreach (ExperienceType::names() as $value => $label)
                <flux:radio :value="$value" :label="$label" />
                @endforeach
            </flux:radio.group>

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

    <livewire:experiences.manage-files />
    <livewire:experiences.manage-skills />
</section>
