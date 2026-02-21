<?php

use App\Models\Profile;
use App\Http\Requests\StoreProfileRequest;
use Flux\Flux;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;
    use WithPagination;

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
    public ?int $salary_desire = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Profile::class);
    }

    public function with(): array
    {
        return [
            'profiles' => auth()->user()->profiles()->paginate(),
        ];
    }

    public function rules(): array
    {
        return (new StoreProfileRequest())->rules();
    }

    public function open(): void
    {
        $this->authorize('create', Profile::class);

        $this->resetForm();

        Flux::modal('profile-modal')->show();
    }

    public function create(): void
    {
        $this->authorize('create', Profile::class);

        $validated = $this->validate();

        if ($this->image instanceof TemporaryUploadedFile) {
            $validated['image'] = $this->image->store('avatars', 'public');
        }

        $profile = Profile::create($validated);

        $this->redirectRoute('profiles.show', $profile, navigate: true);
    }

    public function unsetImage(): void {
        $this->image = null;
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->resetErrorBag();
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
        } elseif ($image !== null) {
            $imageUrl = Storage::url($image);
            $imageLabel = __('Update the image');
        }
    @endphp
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ __('Profiles') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage you profiles') }}</flux:subheading>
        </div>
        <div>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Create new Profile') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <div class="grid lg:grid-cols-2 2xl:grid-cols-3 gap-6">
        @foreach ($profiles as $profile)
        <x-card :href="route('profiles.show', $profile)" wire:key="{{ $profile->id }}" wire:navigate>
            <x-slot:heading class="flex items-center gap-3">
                <flux:avatar size="xs" :src="$profile->image ? Storage::url($profile->image) : null" />
                {{ $profile->name }}
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div class="flex items-center gap-2">
                    @php $experienceCount = $profile->experiences()->count(); @endphp
                    <flux:badge size="sm">{{ $experienceCount }}</flux:badge>
                    {{ trans_choice('Experience|Experiences', $experienceCount) }}
                </div>

                <div class="flex items-center gap-2">
                    @php $skillCount = $profile->skills()->count(); @endphp
                    <flux:badge size="sm">{{ $skillCount }}</flux:badge>
                    {{ trans_choice('Skill|Skills', $skillCount) }}
                </div>
            </div>
        </x-card>
        @endforeach
    </div>

    {{ $profiles->links() }}

    <x-flyout name="profile-modal" wire:close="resetForm">
        <flux:heading size="xl" level="1">{{ __('Create new Profile') }}</flux:heading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="create">
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

            <flux:textarea wire:model="address" :label="__('Address')" />

            <div class="grid grid-cols-2 items-start gap-6">
                <flux:input wire:model="post_code" :label="__('Post code')" />
                <flux:input wire:model="location" :label="__('Location')" />
            </div>

            <div class="grid grid-cols-2 items-start gap-6">
                <flux:input wire:model="birthdate" type="date" :label="__('Birthdate')" />
                <flux:input wire:model="birthplace" :label="__('Birthplace')" />
            </div>

            <div class="grid grid-cols-2 items-start gap-6">
                <flux:input wire:model="phone" :label="__('Phone')" />
                <flux:input wire:model="email" :label="__('Email')" />
            </div>

            <flux:input wire:model="website" :label="__('Website')" />

            <flux:field>
                <flux:label>{{ __('Desired Salary') }}</flux:label>
                <flux:input.group>
                    <flux:input wire:model="salary_desire" />
                    <flux:input.group.suffix>€</flux:input.group.suffix>
                </flux:input.group>
                <flux:error name="salary_desire" />
            </flux:field>

            <div class="inline-flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Create') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>
</section>
