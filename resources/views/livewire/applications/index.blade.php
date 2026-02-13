<?php

use App\Actions\PublishApplication;
use App\Actions\UnpublishApplication;
use App\Models\Application;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public bool $isEditing = false;
    public ?string $applicationId = null;

    public ?string $title = null;
    public ?string $source = null;
    public ?string $text = null;
    public ?string $notes = null;
    public ?string $contact_name = null;
    public ?string $contact_email = null;
    public ?string $contact_phone = null;
    public ?string $company_name = null;
    public ?string $company_address = null;
    public ?string $company_website = null;
    public ?string $profile_id = null;
    public ?string $public_id = null;

    private PublishApplication $publishApplication;
    private UnpublishApplication $unpublishApplication;

    public function boot(
        PublishApplication $publishApplication,
        UnpublishApplication $unpublishApplication
    ) {
        $this->publishApplication = $publishApplication;
        $this->unpublishApplication = $unpublishApplication;
    }

    public function with(): array
    {
        return [
            'applications' => Auth::user()->applications()->paginate(),
        ];
    }

    public function open(?string $id = null): void
    {
        $this->authorize('create', Application::class);

        $this->resetForm();

        if (Str::isUlid($id)) {
            $this->isEditing = true;
            $this->applicationId = $id;

            $application = Application::findOrFail($id);

            $this->title = $application->title;
            $this->source = $application->source;
            $this->text = $application->text;
            $this->notes = $application->notes;

            $this->contact_name = $application->contact_name;
            $this->contact_email = $application->contact_email;
            $this->contact_phone = $application->contact_phone;

            $this->company_name = $application->company_name;
            $this->company_address = $application->company_address;
            $this->company_website = $application->company_website;

            $this->public_id = $application->public_id;
            $this->profile_id = $application->profile?->id;

        }

        Flux::modal('application-modal')->show();
    }

    public function save(): void
    {
        $applications = Auth::user()->applications();
        $request = Str::isUlid($this->applicationId) ? new UpdateApplicationRequest() : new StoreApplicationRequest();
        $validated = $this->validate(rules: $request->rules());

        if (Str::isUlid($this->applicationId)) {
            $applications->where('id', $this->applicationId)->update($validated);
        } else {
            $applications->forceCreate($validated);
        }

        Flux::modal('application-modal')->close();

        $this->resetForm();
    }

    public function publish(?string $id = null): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $application = Auth::user()->applications()->find($id);

        if (!$application instanceof Application) {
            return;
        }

        $this->publishApplication->handle($application);
    }

    public function unpublish(?string $id = null): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $application = Auth::user()->applications()->find($id);

        if (!$application instanceof Application) {
            return;
        }

        $this->unpublishApplication->handle($application);
    }

    public function delete(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $application = Auth::user()->applications()->find($id);

        if (!$application instanceof Application) {
            return;
        }

        $application->delete();
    }

    public function resetForm(): void
    {
        parent::reset();
        parent::resetErrorBag();
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ __('Applications') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage you applications') }}</flux:subheading>
        </div>
        <div>
            <flux:button icon="plus" variant="primary" :loading="false" wire:click="open">
                {{ __('Create') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <div class="space-y-4">

        @foreach ($applications as $application)
        <flux:callout class="p-3" inline>
            <flux:callout.heading class="text-base!">
                <a href="{{ route('applications.show', $application) }}" class="hover:text-accent hover:underline" wire:navigate>
                    {{ $application->company_name }}
                </a>
            </flux:callout.heading>

            <x-slot name="actions" class="me-4! self-center!">
                @if ($application->isPublic())
                <flux:button icon="globe-europe-africa" class="font-mono" size="sm" variant="primary" color="lime" target="_blank" rel="noopener" :href="config('app.frontend_url') . '/' . $application->public_id">
                    {{ $application->public_id }}
                </flux:button>
                @else
                <flux:button icon="eye-slash" size="sm" disabled>
                    {{ __('Not public') }}
                </flux:button>
                @endif
            </x-slot>

            <x-slot name="controls">
                <flux:dropdown class="ms-auto">
                    <flux:button icon="ellipsis-horizontal" variant="filled" />

                    <flux:menu>
                        <flux:menu.item icon="pencil-square" wire:click="open('{{ $application->id }}')">
                            {{ __('Edit') }}
                        </flux:menu.item>

                        @if ($application->isPublic())
                        <flux:menu.item icon="eye" wire:click="unpublish('{{ $application->id }}')">
                            {{ __('Unpublish') }}
                        </flux:menu.item>
                        @else
                        <flux:menu.item icon="eye-slash" wire:click="publish('{{ $application->id }}')">
                            {{ __('Publish') }}
                        </flux:menu.item>
                        @endif

                        <flux:menu.item variant="danger" icon="trash" wire:click="delete('{{ $application->id }}')" wire:confirm="{{ __('Are you sure you want to delete this application?') }}">
                            {{ __('Delete') }}
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </x-slot>
        </flux:callout>
        @endforeach

    </div>

    <x-flyout name="application-modal" wire:close="resetForm">

        <flux:heading size="xl" level="1">
            {{ __($isEditing ? 'Edit application' : 'Create new application') }}
        </flux:heading>

        <flux:separator variant="subtle" />

        <form class="space-y-16" wire:submit="save">
            <div class="space-y-6">
                <div class="grid lg:grid-cols-2 gap-6">
                    <flux:input wire:model="title" :label="__('Title')" />
                    <flux:input wire:model="source" :label="__('Source')" type="url" icon="link" />
                </div>

                <flux:select wire:model="profile_id" :label="__('Profile')" required>
                    <flux:select.option value="" selected hidden>{{ __('Choose profile...') }}</flux:select.option>
                    @foreach (Auth::user()->profiles()->pluck('name',  'id') as $id => $name)
                    <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model="text" :label="__('Text')" />

                <flux:textarea wire:model="notes" :label="__('Notes')" />
            </div>

            <div class="space-y-6">
                <div>
                    <flux:heading size="xl" class="mb-2">{{ __('Company') }}</flux:heading>
                    <flux:separator variant="subtle" />
                </div>

                <div class="grid lg:grid-cols-2 gap-6">
                    <flux:input wire:model="company_name" :label="__('Company name')" icon="building-office" />
                    <flux:input wire:model="company_website" :label="__('Company website')" type="url" icon="link" />
                </div>

                <flux:textarea wire:model="company_address" :label="__('Company address')" />
            </div>

            <div class="space-y-6">
                <div>
                    <flux:heading size="xl" class="mb-2">{{ __('Contact') }}</flux:heading>
                    <flux:separator variant="subtle" />
                </div>

                <flux:input wire:model="contact_name" :label="__('Contact name')" icon="user" />

                <div class="grid lg:grid-cols-2 gap-6">
                    <flux:input wire:model="contact_email" :label="__('Contact email')" type="email" icon="at-symbol" />
                    <flux:input wire:model="contact_phone" :label="__('Contact phone')" type="tel" icon="phone" />
                </div>
            </div>

            <div class="flex items-center justify-start gap-4">
                <flux:button variant="primary" type="submit">{{ __($isEditing ? 'Save' : 'Create') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>

    </x-flyout>
</section>
