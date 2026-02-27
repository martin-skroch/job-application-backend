<?php

use App\Models\Content;
use App\Models\Profile;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use Flux\Flux;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Profile $profile;

    public bool $isEditing = false;
    public ?string $contentId = null;

    public ?string $heading = null;
    public ?string $name = null;
    public ?string $text = null;
    public $image = null;
    public $currentImage = null;
    public ?string $order = null;
    public bool $active = false;

    public function mount(Profile $profile): void
    {
        $this->profile = $profile;

        $this->authorize('view', $this->profile);
    }

    public function open(?string $id = null): void
    {
        $this->resetForm();

        $this->isEditing = Str::isUlid($id);

        // TODO:
        $contents = $this->profile->contents();

        if ($this->isEditing) {
            $this->contentId = $id;

            $content = $contents->where('id', $id)->firstOrFail();

            $this->name = $content->name;
            $this->heading = $content->heading;
            $this->text = $content->text;
            $this->image = $content->image;
            $this->currentImage = $content->image;
            $this->order = $content->order;
            $this->active = $content->active;
        } else {
            $lastOrderNumber = $contents->select('order')->latest('order')->first()?->order;

            $this->heading = 'Wer bin ich?';
            $this->name = $this->slugify($this->heading);
            $this->text = 'Lorem ipsum dolor sit amet.';
            $this->image = null;
            $this->order = $lastOrderNumber + 1;
            $this->active = true;
        }

        Flux::modal('content-modal')->show();
    }

    public function save(): void
    {
        $isEditing = $this->contentId !== null && Str::isUlid($this->contentId);
        $request = $isEditing ? new UpdateContentRequest() : new StoreContentRequest();
        $imageToDelete = null;

        if ($this->image !== $this->currentImage) {
            $imageToDelete = $this->currentImage;
        }

        if ($this->image !== null && !$this->image instanceof TemporaryUploadedFile) {
            $this->reset('image');
        }

        $validated = $this->validate($request->rules());
        $contents = $this->profile->contents();


        if ($this->image instanceof TemporaryUploadedFile) {
            $validated['image'] = $this->image->store('contents', 'public');

            if (filled($this->currentImage)) {
                $imageToDelete = $this->currentImage;
            }
        }

        if (filled($imageToDelete) && Storage::exists($imageToDelete)) {
            Storage::delete($imageToDelete);
        }

        if ($isEditing) {
            $contents->where('id', $this->contentId)->update($validated);
        } else {
            $contents->create($validated);
        }

        Flux::modal('content-modal')->close();

        $this->resetForm();
    }

    public function delete(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $content = $this->profile->contents()->find($id);

        if (!$content instanceof Content) {
            return;
        }

        if (filled($content->image) && Storage::exists($content->image)) {
            Storage::delete($content->image);
        }

        $content->delete();
    }

    public function toggleActive(string $id, bool $active = false): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $content = $this->profile->contents()->find($id);

        if (!$content instanceof Content) {
            return;
        }

        $content->update(['active' => !$active]);
    }

    public function updatedHeading(): void
    {
        if (!$this->isEditing) {
            $this->name = $this->slugify($this->heading);
        }
    }

    public function unsetImage(): void {
        $this->image = null;
    }

    public function updateOrder(array $items): void
    {
        foreach ($items as $item) {
            Content::where([
                'id' => $item['id'],
                'profile_id' => $this->profile->id,
            ])->update(['order' => $item['order']]);
        }
    }

    public function resetForm(): void
    {
        // $properties = collect(parent::all());
        // $properties->forget('profile');

        parent::reset('isEditing', 'contentId', 'name', 'heading', 'text', 'image', 'active', 'order');

        parent::resetErrorBag();
    }

    private function slugify(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return Str::slug($value, '-', App::getLocale());
    }
}; ?>

<section class="space-y-6">
    <x-pages::profiles.layout :profile="$profile" :heading="__('Contents')" :subheading="__('Manage your contents.')">

        <x-slot:actions>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Add Content') }}
            </flux:button>
        </x-slot>

        <div class="space-y-6" x-sort x-on:sort.stop="$wire.updateOrder(Array.from($el.children).map((el, index) => ({id: el.dataset.id, order: index + 1})))">
            @foreach ($profile->contents as $content)
            <flux:callout class="group{{ !$content->active ? ' opacity-60 inactive' : '' }}" inline :data-id="$content->id">
                <div class="flex max-sm:flex-col gap-2">
                    <div class="relative flex items-center justify-center size-20 rounded-md text-gray-900/20 dark:text-neutral-100/20 bg-zinc-200 dark:bg-zinc-700 me-2">
                        @if ($content->image)
                            <img src="{{ $content->image ? Storage::url($content->image) : null }}" class="absolute inset-0 size-full object-cover rounded-md group-[.inactive]:grayscale">
                        @else
                            <flux:icon name="photo" class="size-8" />
                        @endif
                    </div>

                    <div class="space-y-2">
                        <div class="text-lg font-medium">{{ $content->heading }}</div>

                        @if ($content->text)
                        <flux:callout.text>{{ $content->text }}</flux:callout.text>
                        @endif
                    </div>
                </div>

                <x-slot name="actions">
                    <flux:dropdown>
                        <flux:button icon="ellipsis-horizontal" variant="ghost" />

                        <flux:menu>
                            <flux:menu.item icon="pencil-square" wire:click="open('{{ $content->id }}')">
                                {{ __('Edit') }}
                            </flux:menu.item>

                            <flux:menu.item icon="{{ $content->active ? 'eye-slash' : 'eye' }}" wire:click="toggleActive('{{ $content->id }}', {{ $content->active }})">
                                {{ $content->active ? __('Deactivate') : __('Activate') }}
                            </flux:menu.item>

                            <flux:menu.item variant="danger" icon="trash" wire:click="delete('{{ $content->id }}')" wire:confirm="{{ __('Are you sure you want to delete this content?') }}">
                                {{ __('Delete') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>

                    <flux:button icon="chevron-up-down" variant="ghost" x-sort:handle />
                </x-slot>
            </flux:callout>
            @endforeach
        </div>

    </x-pages::profiles.layout>

    <x-flyout name="content-modal">
        <flux:heading size="xl" level="1">{{ $this->isEditing ? __('Edit') : __('Create') }}</flux:heading>
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
            <flux:input type="text" wire:model.live="heading" :label="__('Heading')" />
            <flux:input type="text" wire:model.live="name" :label="__('Name')" />
            <flux:textarea wire:model="text" :label="__('Text')" resize="vertical" />

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

            <flux:switch wire:model="active" :label="__('Active')" align="left" />

            <div class="inline-flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>
</section>
