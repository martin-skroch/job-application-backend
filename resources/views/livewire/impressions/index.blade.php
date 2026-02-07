<?php

use App\Http\Requests\StoreImpressionRequest;
use App\Http\Requests\UpdateImpressionRequest;
use App\Models\Impression;
use App\Models\Profile;
use Flux\Flux;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    public Profile $profile;

    public bool $isEditing = false;
    public ?string $impressionId = null;

    public $currentImage = null;
    public $image = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?string $order = null;
    public bool $active = false;

    public function open(?string $id = null): void
    {
        $this->resetForm();

        $this->isEditing = Str::isUlid($id);
        $impressions = $this->profile->impressions();

        if ($this->isEditing) {
            $this->impressionId = $id;

            $impression = $impressions->where('id', $id)->firstOrFail();

            $this->currentImage = $impression->image;
            $this->image = $impression->image;
            $this->title = $impression->title;
            $this->description = $impression->description;
            $this->order = $impression->order;
            $this->active = $impression->active;

        } else {
            $lastOrderNumber = $impressions->select('order')->latest('order')->first()?->order;
            $this->order = $lastOrderNumber + 1;
        }

        Flux::modal('impression-modal')->show();
    }

    public function save(): void
    {
        $hasId = $this->impressionId !== null && Str::isUlid($this->impressionId);
        $request = $hasId ? new UpdateImpressionRequest() : new StoreImpressionRequest();

        if ($this->image !== false && !$this->image instanceof TemporaryUploadedFile) {
            $this->reset('image');
        }

        $validated = $this->validate($request->rules());
        $impressions = $this->profile->impressions();

        if ($this->image instanceof TemporaryUploadedFile) {
            $validated['image'] = $this->image->store('impressions', 'public');

            if ($this->currentImage !== null && Storage::exists($this->currentImage)) {
                Storage::delete($this->currentImage);
            }
        } else {
            unset($validated['image']);
        }

        if (Str::isUlid($this->impressionId)) {
            $impressions->where('id', $this->impressionId)->update($validated);
        } else {
            $impressions->create($validated);
        }

        Flux::modal('impression-modal')->close();

        $this->resetForm();
    }

    public function delete(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $impression = $this->profile->impressions()->find($id);

        if (!$impression instanceof Impression) {
            return;
        }

        if (Storage::exists($impression->image)) {
            Storage::delete($impression->image);
        }

        $impression->delete();
    }

    public function toggleActive(string $id, bool $active = false): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $impression = $this->profile->impressions()->find($id);

        if (!$impression instanceof Impression) {
            return;
        }

        $impression->update(['active' => !$active]);
    }

    public function unsetImage(): void {
        $this->image = false;
    }

    public function updateOrder(array $items): void
    {
        foreach ($items as $item) {
            Impression::where([
                'id' => $item['id'],
                'profile_id' => $this->profile->id,
            ])->update(['order' => $item['order']]);
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'isEditing',
            'impressionId',
            'image',
            'title',
            'description',
            'order',
            'active'
        ]);

        $this->resetErrorBag();
    }
}; ?>

<section class="space-y-6">
    <x-profiles.layout :profile="$profile" :heading="__('Skills')" :subheading="__('Manage your impressions.')">
        <x-slot:actions>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Add Impression') }}
            </flux:button>
        </x-slot:actions>

        <div class="space-y-6" x-sort x-on:sort.stop="$wire.updateOrder(Array.from($el.children).map((el, index) => ({id: el.dataset.id, order: index + 1})))">
            @foreach ($profile->impressions as $impression)
            <flux:callout class="group{{ !$impression->active ? ' opacity-60 inactive' : '' }}" inline :data-id="$impression->id" x-sort:item>
                <x-slot name="icon">
                    <img src="{{ $impression->image ? Storage::url($impression->image) : null }}" class="size-16 aspect-square object-cover rounded-md me-2 group-[.inactive]:grayscale">
                </x-slot>

                <flux:callout.heading><div class="text-lg">{{ $impression->title }}</div></flux:callout.heading>

                @if ($impression->description)
                <flux:callout.text>{{ $impression->description }}</flux:callout.text>
                @endif

                <x-slot name="actions">
                    <flux:button size="sm" variant="danger" wire:click="delete('{{ $impression->id }}')" wire:confirm="{{ __('Are you sure you want to delete this impression?') }}">
                        {{ __('Delete') }}
                    </flux:button>

                    <flux:button size="sm" variant="filled" wire:click="open('{{ $impression->id }}')">
                        {{ __('Edit') }}
                    </flux:button>

                    <flux:button size="sm" wire:click="toggleActive('{{ $impression->id }}', {{ $impression->active }})">
                        <span class="inline-flex size-2.5 rounded-full {{ $impression->active ? 'bg-emerald-400' : 'bg-zinc-500' }}"></span>
                        <span class="ms-2">{{ $impression->active ? __('Deactivate') : __('Activate') }}</span>
                    </flux:button>

                    <button class="p-2 opacity-60 group-hover:opacity-100 cursor-move" x-sort:handle>
                        <flux:icon name="chevron-up-down" />
                    </button>

                    <flux:badge>{{ $impression->order }}</flux:badge>
                </x-slot>
            </flux:callout>
            @endforeach
        </div>

        <x-flyout name="impression-modal" wire:close="resetForm">
            <flux:heading size="xl" level="1">{{ Str::isUlid($impressionId) ? __('Edit') : __('Create') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage your impressions') }}</flux:subheading>
            <flux:separator variant="subtle" />

            @php
                $imageUrl = null;
                $imageName = null;
                $imageLabel = __('Upload an image');

                if ($image instanceof TemporaryUploadedFile) {
                    $imageUrl = $image->temporaryUrl();
                    $imageName = $image->getClientOriginalName();
                } elseif ($image) {
                    $imageUrl = Storage::url($image);
                    $imageLabel = __('Update the image');
                }
            @endphp

            <form class="space-y-6" wire:submit="save">
                <flux:field>
                    <flux:label>{{ __('Image') }}</flux:label>

                    <flux:input.group class="relative">
                        <flux:avatar class="rounded-e-none" :src="$imageUrl" />
                        <input wire:model="image" class="absolute inset-0 opacity-0 z-10" type="file">
                        <flux:input class="rounded-s-none" :placeholder="$imageName ?? $imageLabel" />
                        @if($imageUrl)
                        <flux:button icon="trash" iconVariant="micro" class="relative z-20" wire:click="unsetImage"></flux:button>
                        @endif
                    </flux:input.group>

                    <flux:error name="image" />
                </flux:field>

                {{-- <flux:input wire:model="image" type="file" :label="__('Image')" required /> --}}

                <flux:input wire:model="title" type="text" :label="__('Title')" required />

                <flux:textarea wire:model="description" :label="__('Description')" />

                <flux:input wire:model="order" type="text" :label="__('Order')" />

                <flux:switch wire:model="active" :label="__('Active')" align="left" />

                <div class="inline-flex items-center gap-4">
                    <flux:button variant="primary" type="submit">{{ $isEditing ? 'Save' : __('Add') }}</flux:button>
                    <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </x-flyout>
    </x-profiles.layout>

</div>
