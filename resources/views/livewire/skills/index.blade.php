<?php

use App\Http\Requests\StoreSkillRequest;
use App\Http\Requests\UpdateSkillRequest;
use App\Models\Profile;
use App\Models\Skill;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {
    public Profile $profile;

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

            $skill = $this->profile->skills()->where('id', $id)->firstOrFail();

            $this->name = $skill->name;
            $this->info = $skill->info;
            $this->rating = $skill->rating;
            $this->order = $skill->order;
        } else {
            $lastOrderNumber = $this->profile->skills()->select('order')->latest('order')->first()?->order;
            $this->order = $lastOrderNumber + 1;
        }

        Flux::modal('skill-modal')->show();
    }

    public function save(): void
    {
        if (Str::isUlid($this->skillId)) {
            $validated = $this->validate((new UpdateSkillRequest())->rules());
            $this->profile->skills()->where('id', $this->skillId)->update($validated);
        } else {
            $validated = $this->validate((new StoreSkillRequest())->rules());
            $this->profile->skills()->create($validated);
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

    public function updateOrder(array $items): void
    {
        foreach ($items as $item) {
            $skill = Skill::where('id', $item['id'])->where('profile_id', $this->profile->id);
            $skill->update(['order' => $item['order']]);
        }
    }
}; ?>
<section class="space-y-6">
    <x-profiles.layout :profile="$profile" :heading="__('Skills')" :subheading="__('Manage your skills.')">
        <x-slot:actions>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Add Skill') }}
            </flux:button>
        </x-slot>

        <div class="flex flex-col gap-px" x-sort x-on:sort.stop="$wire.updateOrder(Array.from($el.children).map((el, index) => ({id: el.dataset.id, order: index + 1})))">

            @foreach ($profile->skills as $skill)
            <flux:callout class="border-0 not-first:rounded-t-none not-last:rounded-b-none p-0!" :data-id="$skill->id" x-sort:item inline>

                <div class="grid grid-cols-4 items-center text-sm">
                    <div class="col-span-1 flex gap-2 items-center">
                        <button class="cursor-move me-2 text-zinc-500 hover:text-zinc-300" x-sort:handle>
                            <flux:icon name="chevron-up-down" />
                        </button>

                        <span class="font-medium">{{ $skill->name }}</span>

                        @if($skill->info)
                            <small class="text-zinc-500">({{ $skill->info }})</small>
                        @endif
                    </div>

                    <div class="col-span-1 text-center">
                        <div class="bg-zinc-200 dark:bg-zinc-700 rounded overflow-hidden relative">
                            <div class="bg-(--color-accent) w-(--width) h-2" style="--width:{{ $skill->ratingInPercent }}%"></div>
                            <div class="absolute inset-0 flex items-center justify-center"></div>
                        </div>
                    </div>

                    <div class="col-span-1 text-end font-mono">
                        <flux:badge size="sm">{{ $skill->order }}</flux:badge>
                    </div>

                    <div class="col-span-1 text-end -me-2">
                        <flux:button variant="ghost" size="sm" wire:click="open('{{ $skill->id }}')" square>
                            <flux:icon name="pencil-square" class="size-4" />
                        </flux:button>
                    </div>
                </div>
            </flux:callout>
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

                <flux:button class="mb-0" variant="danger" wire:click="delete('{{ $skillId }}')" wire:confirm="{{ __('Are you sure you want to delete this skill?') }}">
                    {{ __('Delete') }}
                </flux:button>
            @endif
        </x-flyout>
    </x-profiles.layout>
</section>
