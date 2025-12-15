<?php

use App\Http\Requests\UpdateResumeRequest;
use App\Models\User;
use App\Models\Resume;
use Flux\Flux;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public User $user;
    public Resume $resume;

    public ?string $name = null;
    public $image = null;
    public ?string $address = null;
    public ?string $post_code = null;
    public ?string $location = null;
    public ?string $birthdate = null;
    public ?string $birthplace = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $website = null;

    public function mount(): void
    {
        $this->authorize('view', $this->resume);

        $this->user = auth()->user();
        $this->name = $this->resume->name;
        $this->address = $this->resume->address;
        $this->post_code = $this->resume->post_code;
        $this->location = $this->resume->location;
        $this->birthdate = $this->resume->birthdate?->format('Y-m-d');
        $this->birthplace = $this->resume->birthplace;
        $this->phone = $this->resume->phone;
        $this->email = $this->resume->email;
        $this->website = $this->resume->website;
    }

    public function rules(): array
    {
        return (new UpdateResumeRequest())->rules();
    }

    public function open(): void
    {
        $this->authorize('update', $this->resume);

        Flux::modal('resume-modal')->show();
    }

    public function save(): void
    {
        $this->authorize('update', $this->resume);

        $validated = $this->validate();

        if ($this->image instanceof TemporaryUploadedFile) {
            $validated['image'] = $this->image->store('avatars', 'public');
        } else {
            unset($validated['image']);
        }

        $this->resume->fill($validated);
        $this->resume->save();

        $this->close();

        $this->dispatch('resume-saved');
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->resume);

        $this->resume->delete();

        $this->close();

        $this->redirectRoute('resumes.index', navigate: true);
    }

    public function unsetImage(): void
    {
        $this->resume->image = null;
        $this->image = null;
    }

    public function close(): void
    {
        Flux::modal('resume-modal')->close();
    }
}; ?>
<section class="space-y-6">
    @php
        $imageUrl = null;
        $imageName = null;
        $imageLabel = __('Upload an image');

        if ($image instanceof TemporaryUploadedFile) {
            $imageUrl = $image->temporaryUrl();
            $imageName = $image->getClientOriginalName();
        } elseif ($resume->image !== null) {
            $imageUrl = Storage::url($resume->image);
            $imageLabel = __('Update the image');
        }
    @endphp

    <x-resumes.layout :resume="$resume" :heading="__('Overview')" :subheading="__('Manage your personal data.')">
        <x-slot:actions>
            <x-action-message on="resume-saved">
                {{ __('Saved.') }}
            </x-action-message>

            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Edit') }}
            </flux:button>
        </x-slot>

        <div class="space-y-6">
            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Image') }}</div>
                <div class="col-span-4">
                    <flux:avatar class="xl" :src="$imageUrl" />
                </div>
            </div>

            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Name') }}</div>
                <div class="col-span-4">{{ $resume->name }}</div>
            </div>

            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Address') }}</div>
                <div class="col-span-4">{{ $resume->address }}<br>{{ $resume->post_code }} {{ $resume->location }}</div>
            </div>

            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Birthdate') }}</div>
                <div class="col-span-4">{{ $resume->birthdate?->isoFormat('LL') ?? '-' }}</div>
            </div>

            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Birthplace') }}</div>
                <div class="col-span-4">{{ $resume->birthplace ?? '-' }}</div>
            </div>

            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Phone') }}</div>
                <div class="col-span-4">{{ $resume->phone ?? '-' }}</div>
            </div>

            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Email') }}</div>
                <div class="col-span-4">{{ $resume->email ?? '-' }}</div>
            </div>

            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Website') }}</div>
                <div class="col-span-4">{{ $resume->website ?? '-' }}</div>
            </div>
        </div>
    </x-resumes.layout>

    <x-flyout name="resume-modal">
        <flux:heading size="xl" level="1">{{ __('Edit') }}</flux:heading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="save">
            <flux:input wire:model="name" :label="__('Name')" type="text" />

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

            <flux:textarea wire:model="address" rows="2" :label="__('Address')" />

            <div class="grid min-2xl:grid-cols-2 items-start gap-6">
                <flux:input wire:model="post_code" :label="__('Post code')" />
                <flux:input wire:model="location" :label="__('Location')" />
            </div>

            <div class="grid min-2xl:grid-cols-2 items-start gap-6">
                <flux:input wire:model="birthdate" type="date" :label="__('Birthdate')" />
                <flux:input wire:model="birthplace" :label="__('Birthplace')" />
            </div>

            <div class="grid min-2xl:grid-cols-2 items-start gap-6">
                <flux:input wire:model="phone" :label="__('Phone')" />
                <flux:input wire:model="email" :label="__('Email')" />
            </div>

            <flux:input wire:model="website" :label="__('Website')" />

            <div class="inline-flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>

        <flux:separator variant="subtle" />

        <flux:button class="mb-0" variant="danger" wire:click="delete" wire:confirm="{{ __('Are you sure you want to delete this resume?') }}">
            {{ __('Delete') }}
        </flux:button>
    </x-flyout>
</section>
