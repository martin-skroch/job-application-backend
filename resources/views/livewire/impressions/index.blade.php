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

    public $image = null;
    public ?string $title = null;
    public ?string $description = null;
    public ?int $order = null;
    public bool $active = false;

    public function updating($property, $value)
    {
        if ($property === 'image' && $value instanceof TemporaryUploadedFile) {
            $this->currentImage = $value;
        }
    }

    public function open(?string $id = null): void
    {
        $this->resetForm();

        $this->isEditing = Str::isUlid($id);
        $impressions = $this->profile->impressions();

        if ($this->isEditing) {
            $this->impressionId = $id;

            $impression = $impressions->where('id', $id)->firstOrFail();

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
        // $this->reset('image');
        $this->image = false;
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

        <div class="space-y-6">
            @foreach ($profile->impressions as $impression)
            <div class="grid grid-cols-6 gap-8{{ !$impression->active ? ' opacity-60' : '' }}">
                <img class="col-span-1 w-full aspect-square object-cover" src="{{ $impression->image ? Storage::url($impression->image) : null }}" alt="{{ $impression->title }}">
                <div class="col-span-3 space-y-4">
                    <h3 class="text-2xl font-bold">{{ $impression->title }}</h3>
                    <p class="">{{ $impression->description }}</p>
                </div>
                <div class="col-span-2 text-end flex gap-2 justify-end">
                    <flux:button wire:click="toggleActive('{{ $impression->id }}', {{ $impression->active }})">
                        <span class="inline-flex size-2.5 rounded-full {{ $impression->active ? 'bg-emerald-400' : 'bg-zinc-500' }}"></span>
                        <span class="ms-2">{{ $impression->active ? __('Deactivate') : __('Activate') }}</span>
                    </flux:button>

                    <flux:button variant="filled" wire:click="open('{{ $impression->id }}')">
                        {{ __('Edit') }}
                    </flux:button>

                    <flux:button variant="danger" wire:click="delete('{{ $impression->id }}')" wire:confirm="{{ __('Are you sure you want to delete this impression?') }}">
                        {{ __('Delete') }}
                    </flux:button>
                </div>
            </div>
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

            @dump($this)

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
