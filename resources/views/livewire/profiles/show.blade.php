<?php

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Models\Profile;
use Flux\Flux;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public User $user;
    public Profile $profile;

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

    public ?string $deleteImage = null;

    public function mount(): void
    {
        $this->authorize('view', $this->profile);

        $this->user = auth()->user();
        $this->name = $this->profile->name;
        $this->image = $this->profile->image;
        $this->address = $this->profile->address;
        $this->post_code = $this->profile->post_code;
        $this->location = $this->profile->location;
        $this->birthdate = $this->profile->birthdate?->format('Y-m-d');
        $this->birthplace = $this->profile->birthplace;
        $this->phone = $this->profile->phone;
        $this->email = $this->profile->email;
        $this->website = $this->profile->website;
        $this->salary_desire = $this->profile->salary_desire;
    }

    public function rules(): array
    {
        $rules = (new UpdateProfileRequest())->rules();

        if (is_string($this->image) && strlen($this->image) > 0) {
            unset($rules['image']);
        }

        return $rules;
    }

    public function open(): void
    {
        $this->authorize('update', $this->profile);

        Flux::modal('profile-modal')->show();
    }

    public function save(): void
    {
        $this->authorize('update', $this->profile);

        $validated = $this->validate();

        if ($this->image instanceof TemporaryUploadedFile) {
            $validated['image'] = $this->image->store('avatars', 'public');
            $this->profile->image = $validated['image'];
        }

        if ($this->deleteImage !== null && Storage::exists($this->deleteImage)) {
            Storage::delete($this->deleteImage);
            $this->deleteImage = null;
        }

        $this->profile->fill($validated);
        $this->profile->save();

        Flux::modal('profile-modal')->close();

        $this->dispatch('profile-saved');
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->profile);

        $this->profile->delete();

        Flux::modal('profile-modal')->close();

        $this->redirectRoute('profiles.index', navigate: true);
    }

    public function unsetImage(): void
    {
        $this->deleteImage = $this->profile->image;
        $this->profile->image = null;
        $this->image = null;
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
        } elseif ($profile->image !== null) {
            $imageUrl = Storage::url($profile->image);
            $imageLabel = __('Update the image');
        }
    @endphp

    <x-profiles.layout :profile="$profile" :heading="__('Overview')" :subheading="__('Manage your personal data.')">
        <x-slot:actions>
            <x-action-message on="profile-saved">
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
                <div class="col-span-4">{{ $profile->name }}</div>
            </div>

            @if(!empty($profile->address) || !empty($profile->post_code) || !empty($profile->location))
            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Address') }}</div>
                <div class="col-span-4">
                    @empty(!$profile->address){{ $profile->address }}<br>@endempty
                    {{ $profile->post_code }} {{ $profile->location }}
                </div>
            </div>
            @endif

            @if(!empty($profile->birthdate))
            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Birthdate') }}</div>
                <div class="col-span-4">{{ $profile->birthdate?->isoFormat('LL') }}</div>
            </div>
            @endif

            @if(!empty($profile->birthplace))
            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Birthplace') }}</div>
                <div class="col-span-4">{{ $profile->birthplace }}</div>
            </div>
            @endif

            @if(!empty($profile->phone))
            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Phone') }}</div>
                <div class="col-span-4">{{ $profile->phone }}</div>
            </div>
            @endif

            @if(!empty($profile->email))
            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Email') }}</div>
                <div class="col-span-4">
                    <a class="text-accent-content hover:underline" href="mailto:{{ $profile->email }}" target="_blank" rel="noopener">
                        {{ $profile->email }}
                    </a>
                </div>
            </div>
            @endif

            @if(!empty($profile->website))
            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Website') }}</div>
                <div class="col-span-4">
                    <a class="text-accent-content hover:underline" href="{{ route('redirect', ['url' => $profile->website]) }}" target="_blank" rel="noopener">
                        {{ $profile->website }}
                    </a>
                </div>
            </div>
            @endif

            @if(!empty($profile->salary_desire))
            <div class="grid xl:grid-cols-5 items-start gap-1 xl:gap-6">
                <div class="col-span-1 font-bold">{{ __('Desired Salary') }}</div>
                <div class="col-span-4">{{ Number::currency($profile->salary_desire, precision: 0) }}</div>
            </div>
            @endif
        </div>
    </x-profiles.layout>

    <x-flyout name="profile-modal">
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

            <div class="grid 2xl:grid-cols-2 items-start gap-6">
                <flux:input wire:model="post_code" :label="__('Post code')" />
                <flux:input wire:model="location" :label="__('Location')" />
            </div>

            <div class="grid 2xl:grid-cols-2 items-start gap-6">
                <flux:input wire:model="birthdate" type="date" :label="__('Birthdate')" />
                <flux:input wire:model="birthplace" :label="__('Birthplace')" />
            </div>

            <div class="grid 2xl:grid-cols-2 items-start gap-6">
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
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>

        <flux:separator variant="subtle" />

        <flux:button class="mb-0" variant="danger" wire:click="delete" wire:confirm="{{ __('Are you sure you want to delete this profile?') }}">
            {{ __('Delete') }}
        </flux:button>
    </x-flyout>
</section>
