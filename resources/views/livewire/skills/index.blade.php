<?php

use App\Http\Requests\StoreSkillRequest;
use App\Http\Requests\UpdateSkillRequest;
use App\Models\Resume;
use App\Models\Skill;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public Resume $resume;

    public bool $isEditing = false;
    public ?string $skillId = null;

    public string $name = '';
    public ?string $info = '';
    public ?int $rating = null;
    public string $order = '';

    public function open(?string $id = null): void
    {
        $this->resetForm();

        $this->isEditing = Str::isUlid($id);

        if ($this->isEditing) {
            $this->skillId = $id;

            $skill = $this->resume->skills()->where('id', $id)->firstOrFail();

            $this->name = $skill->name;
            $this->info = $skill->info;
            $this->rating = $skill->rating;
            $this->order = $skill->order;
        } else {
            $lastOrderNumber = $this->resume->skills()->select('order')->latest('order')->first()?->order;
            $this->order = $lastOrderNumber + 1;
        }

        Flux::modal('skill-modal')->show();
    }

    public function save(): void
    {
        if (Str::isUlid($this->skillId)) {
            $validated = $this->validate((new UpdateSkillRequest())->rules());
            $this->resume->skills()->where('id', $this->skillId)->update($validated);
        } else {
            $validated = $this->validate((new StoreSkillRequest())->rules());
            $this->resume->skills()->create($validated);
        }

        Flux::modal('skill-modal')->close();

        $this->resetForm();
    }

    public function delete(string $id): void
    {
        $skill = Skill::where('id', $id);
        $skill->delete();

        Flux::modal('skill-modal')->close();
    }

    public function resetForm(): void
    {
        $this->reset([
            'name',
            'info',
            'rating',
            'order',
        ]);

        $this->resetErrorBag();

        $this->isEditing = false;
        $this->skillId = null;
    }
}; ?>
<section class="space-y-6">
    <x-resumes.layout :resume="$resume" :heading="__('Skills')" :subheading="__('Manage your skills.')">
        <x-slot:actions>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Add Skill') }}
            </flux:button>
        </x-slot>

        <div class="space-y-2 -mt-4">
            @foreach ($resume->skills as $skill)
                <div class="grid grid-cols-4 items-center">
                    <div class="col-span-1 flex gap-2 items-center">
                        <span>{{ $skill->name }}</span>
                        @if($skill->info) <small class="text-zinc-500">({{ $skill->info }})</small>@endif
                    </div>

                    <div class="col-span-1 text-center">
                        <div class="bg-zinc-200 dark:bg-zinc-700 rounded overflow-hidden relative">
                            <div class="bg-[var(--color-accent)] w-[var(--width)] h-4" style="--width:{{ $skill->ratingInPercent }}%"></div>
                            <div class="absolute inset-0 text-[0.65rem] font-mono flex items-center justify-center"></div>
                        </div>
                    </div>

                    <div class="col-span-1 text-end font-mono">
                        <flux:badge size="sm">{{ $skill->order }}</flux:badge>
                    </div>

                    <div class="col-span-1 text-end">
                        <flux:button variant="primary" size="sm" wire:click="open('{{ $skill->id }}')">{{ __('Edit') }}</flux:button>
                    </div>
                </div>
                <flux:separator variant="subtle" />
            @endforeach
        </div>

        <x-flyout name="skill-modal" wire:close="resetForm">
            <flux:heading size="xl" level="1">{{ Str::isUlid($skillId) ? __('Edit') : __('Create') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage your skills') }}</flux:subheading>
            <flux:separator variant="subtle" />

            <form class="space-y-6" wire:submit="save">
                <flux:input wire:model="name" :label="__('Name')" type="text" autofocus required />
                <flux:input wire:model="info" :label="__('Info')" type="text" />

                <flux:radio.group wire:model="rating" :label="__('Rating')" variant="segmented">
                    @foreach (range(0, 6) as $step)
                    <flux:radio :value="$step" :label="$step" />
                    @endforeach
                </flux:radio.group>

                <flux:input wire:model="order" :label="__('Order')" type="text" />

                <div class="inline-flex items-center gap-4">
                    <flux:button variant="primary" type="submit">{{ $isEditing ? 'Save' : __('Add') }}</flux:button>
                    <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
                </div>
            </form>

            @if ($skillId)
                <flux:separator variant="subtle" />

                <flux:button class="mb-0" variant="danger" wire:click="delete('{{ $skillId }}')"
                    wire:confirm="{{ __('Are you sure you want to delete this skill?') }}">
                    {{ __('Delete') }}
                </flux:button>
            @endif
        </x-flyout>
    </x-resumes.layout>
</section>
