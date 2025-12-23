<?php

use App\Http\Requests\StoreExperienceRequest;
use App\Http\Requests\UpdateExperienceRequest;
use App\Models\Experience;
use App\Models\Resume;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

new class extends Component {
    public Resume $resume;

    public bool $isEditing = false;

    public string $position = '';
    public ?string $institution = '';
    public ?string $location = '';
    public ?string $type = '';
    public string $entry = '';
    public ?string $exit = null;
    public array $skills = [];
    public ?string $description = '';
    public bool $active = false;

    public ?string $experienceId = null;
    public ?Collection $experienceSkills = null;

    public function mount(): void
    {
        $this->authorize('viewAny', $this->resume);
    }

    public function open(?string $id = null): void
    {
        $this->resetForm();

        $this->isEditing = Str::isUlid($id);

        if ($this->isEditing) {
            $experience = Experience::findOrFail($id);

            $this->experienceId = $experience->id;
            $this->position = $experience->position;
            $this->institution = $experience->institution;
            $this->location = $experience->location;
            $this->type = $experience->type;
            $this->entry = $experience->entry?->format('Y-m-d');
            $this->exit = $experience->exit?->format('Y-m-d');
            $this->skills = $experience->skills->pluck('id')->all();
            $this->description = $experience->description;
            $this->active = $experience->active;
        }

        Flux::modal('experience-modal')->show();
    }

    public function save(): void
    {
        $hasId = $this->experienceId !== null && Str::isUlid($this->experienceId);
        $request = $hasId ? new UpdateExperienceRequest() : new StoreExperienceRequest();
        $experiences = $this->resume->experiences();

        $validated = $this->validate($request->rules($this->resume));

        $skills = $validated['skills'] ?? [];
        unset($validated['skills']);

        if ($hasId) {
            $experience = $experiences->where('id', $this->experienceId)->firstOrFail();
            $experience->update($validated);
        } else {
            $experience = $experiences->create($validated);
        }

        $experience->skills()->sync($skills);

        Flux::modal('experience-modal')->close();

        $this->resetForm();
    }

    public function delete(string $id): void
    {
        $experience = Experience::where('id', $id);

        $experience->delete();

        Flux::modal('experience-modal')->close();
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
            'position',
            'institution',
            'location',
            'type',
            'entry',
            'exit',
            'skills',
            'description',
            'active',
        ]);

        $this->resetErrorBag();

        $this->isEditing = false;
        $this->experienceId = null;
    }

    public function editSkills(string $experienceId): void
    {
        $this->experienceId = $experienceId;
        $this->experienceSkills = Experience::findOrFail($this->experienceId)->skills;

        Flux::modal('experience-skills-modal')->show();
    }

    public function reorderSkills(array $items): void
    {
        $experience = Experience::findOrFail($this->experienceId);

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
    <x-resumes.layout :resume="$resume" :heading="__('Experiences')" :subheading="__('Manage your experiences.')">
        <x-slot:actions>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Add Experience') }}
            </flux:button>
        </x-slot>

        <div class="relative pt-6">
            @foreach ($resume->experiences as $experience)
                <div class="grid grid-cols-5{{ !$experience->active ? ' opacity-60' : '' }}">

                    <div class="col-span-1 px-8 pb-12 space-y-3 text-end">
                        <div class="inline-flex items-center gap-2 whitespace-nowrap font-mono">
                            {{ $experience->entry->format('m/Y') }} -
                            {{ $experience->exit?->format('m/Y') ?? __('Today') }}
                        </div>

                        <div class="flex items-center justify-end gap-2 text-sm text-zinc-500">
                            <flux:icon name="calendar-days" class="size-4" /> {{ $experience->duration }}
                        </div>

                        @if ($experience->location)
                            <div class="flex items-center justify-end gap-2 text-sm text-zinc-500">
                                <flux:icon name="map-pin" class="size-4" /> {{ $experience->location }}
                            </div>
                        @endif

                        <flux:button.group class="mt-4 justify-end">
                            <flux:button size="xs" wire:click="open('{{ $experience->id }}')">
                                {{ __('Edit') }}
                            </flux:button>

                            <flux:button size="xs" wire:click="toggleActive('{{ $experience->id }}', {{ $experience->active }})">
                                <span class="me-1">{{ $experience->active ? __('Active') : __('Inactive') }}</span>
                                <span class="inline-flex size-2 rounded-full {{ $experience->active ? 'bg-emerald-400' : 'bg-zinc-500' }}"></span>
                            </flux:button>
                        </flux:button.group>
                    </div>

                    <div class="col-span-4 px-8 pb-12 space-y-3 relative">
                        <h2 class="text-xl relative">
                            {{ $experience->position }} <flux:badge size="sm">({{ $experience->id }})</flux:badge>
                            <span class="absolute size-4 rounded-full bg-zinc-700 -left-10 top-1.5"></span>
                        </h2>

                        <div class="text-zinc-400 space-y-3">
                            @if ($experience->type)
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="font-bold">{{ __('Type') }}:</div>
                                    <div class="truncate">{{ $experience->type }}</div>
                                </div>
                            @endif

                            @if ($experience->institution)
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="font-bold">{{ __('Institution') }}:</div>
                                    <div class="truncate">{{ $experience->institution }}</div>
                                </div>
                            @endif

                            @if ($experience->description)
                                <div class="text-zinc-400">
                                    <p>{{ $experience->description }}</p>
                                </div>
                            @endif

                            <div class="flex flex-wrap gap-2">
                                @foreach ($experience->skills as $skill)
                                <flux:badge size="sm">{{ $skill->name }}</flux:badge>
                                @endforeach

                                <flux:button variant="primary" size="xs" icon="pencil" wire:click="editSkills('{{ $experience->id }}')">
                                    {{ __('Edit') }}
                                </flux:button>
                            </div>
                        </div>

                        <div
                            class="block absolute h-full w-px rounded-full bg-zinc-700 -translate-x-1/2 left-0 top-2 bottom-2">
                        </div>

                        @if ($loop->last)
                            <div class="block absolute size-3 rotate-45 bg-zinc-700 -left-1.5 -bottom-5"></div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    </x-resumes.layout>

    <x-flyout name="experience-modal" wire:close="resetForm">
        <flux:heading size="xl" level="1">{{ $isEditing ? __('Edit Experience') : __('Add Experience') }}</flux:heading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="save">
            <flux:input wire:model="position" :label="__('Position')" type="text" required />
            <flux:input wire:model="type" :label="__('Type')" type="text" />
            <flux:input wire:model="institution" :label="__('Institution')" type="text" />
            <flux:input wire:model="location" :label="__('Location')" type="text" />

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

            <flux:checkbox.group wire:model.live="skills" class="grid grid-cols-3" :label="__('Skills')" >
                @foreach ($resume->skills as $skill)
                <flux:checkbox :label="$skill->name" :value="$skill->id" />
                @endforeach
            </flux:checkbox.group>

            <flux:textarea wire:model="description" :label="__('Description')" resize="vertical" />

            <flux:switch wire:model="active" :label="__('Active')" align="left" />

            <div class="inline-flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ $isEditing ? 'Save' : __('Add') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}
                </flux:button>
            </div>
        </form>

        @if ($experienceId)
            <flux:separator variant="subtle" />

            <flux:button class="mb-0" variant="danger" wire:click="delete('{{ $experienceId }}')" wire:confirm="{{ __('Are you sure you want to delete this experience?') }}">
                {{ __('Delete') }}
            </flux:button>
        @endif
    </x-flyout>

    <x-flyout name="experience-skills-modal" wire:close="resetSkills">
        <flux:heading size="xl" level="1">{{ __('Edit Skills') }}</flux:heading>
        <flux:separator variant="subtle" />

        <div class="flex flex-col gap-px" x-sort x-on:sort.stop="$wire.reorderSkills(Array.from($el.children).map((el, index) => ({id: el.dataset.id, order: index + 1})))">

            @foreach ($experienceSkills?->all() ?? [] as $skill)
            <flux:callout class="border-0 not-first:rounded-t-none not-last:rounded-b-none p-0!" :data-id="$skill->id" x-sort:item inline>

                <div class="flex items-center text-sm">
                    <div class="flex items-center gap-2 grow">
                        <button class="cursor-move me-2 text-zinc-500 hover:text-zinc-300" x-sort:handle>
                            <flux:icon name="chevron-up-down" />
                        </button>

                        <span class="font-medium">{{ $skill->name }}</span>

                        @if($skill->info)
                            <small class="text-zinc-500">({{ $skill->info }})</small>
                        @endif
                    </div>

                    <div class="col-span-1 text-end font-mono">
                        {{ $skill->pivot->order ?? '' }}
                    </div>
                </div>
            </flux:callout>
            @endforeach

        </div>
    </x-flyout>
</section>
