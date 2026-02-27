<?php

use App\Models\Application;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Application $application;

    public ?string $title = null;
    public ?string $source = null;
    public ?string $description = null;
    public ?string $contact_name = null;
    public ?string $contact_email = null;
    public ?string $contact_phone = null;
    public ?string $company_name = null;
    public ?string $company_address = null;
    public ?string $company_website = null;

    public function mount(): void
    {
        $this->authorize('view', $this->application);
        $this->fillFromApplication();
    }

    private function fillFromApplication(): void
    {
        $this->title = $this->application->title;
        $this->source = $this->application->source?->value();
        $this->description = $this->application->description;
        $this->contact_name = $this->application->contact_name;
        $this->contact_email = $this->application->contact_email;
        $this->contact_phone = $this->application->contact_phone;
        $this->company_name = $this->application->company_name;
        $this->company_address = $this->application->company_address;
        $this->company_website = $this->application->company_website?->value();
    }

    #[Computed]
    public function mapLink(): ?string
    {
        $mapQuery = '';

        if (filled($this->application->company_name)) {
            $mapQuery .= $this->application->company_name . '+';
        }

        if (filled($this->application->company_address)) {
            $mapQuery .= str_replace("\n", ',', $this->application->company_address);
        }

        if (blank($mapQuery)) {
            return null;
        }

        return 'https://www.google.de/maps/search/' . urlencode($mapQuery);
    }

    public function open(): void
    {
        $this->authorize('update', $this->application);
        $this->fillFromApplication();

        Flux::modal('edit-job-listing-modal')->show();
    }

    public function save(): void
    {
        $this->authorize('update', $this->application);

        $validated = $this->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'url:http,https', 'max:255'],
            'description' => ['nullable', 'string'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        $this->application->fill($validated)->save();

        $this->application->refresh();

        Flux::modal('edit-job-listing-modal')->close();

        $this->dispatch('application-saved');
    }
}; ?>

<div x-data="{ open: false }">
    <div class="flex items-center gap-3 py-4 cursor-pointer select-none" x-on:click="open = !open">
        <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
        <flux:heading size="lg">{{ __('Job Listing') }}</flux:heading>
        <flux:button
            icon="pencil-square"
            variant="ghost"
            size="sm"
            wire:click="open"
            x-on:click.stop
            class="ml-auto"
        >
            {{ __('Edit') }}
        </flux:button>
    </div>

    <flux:callout x-show="open" x-transition.opacity class="xl:p-6">
        <div class="flex max-xl:flex-col items-stretch gap-6 xl:gap-12">

            {{-- Company & Contact --}}
            <div class="xl:w-1/4 space-y-6 xl:space-y-12">
                <div class="space-y-6">
                    <flux:heading>{{ __('Company') }}</flux:heading>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</p>
                            @if ($application->company_name)
                                <p>{{ $application->company_name }}</p>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Website') }}</p>
                            @if ($application->company_website)
                                <a href="{{ route('redirect', ['url' => (string) $application->company_website]) }}" target="_blank" rel="noopener" class="break-all text-accent hover:underline truncate">
                                    {{ str($application->company_website)->replaceMatches('#^https?://#i', '$1')->rtrim('/') }}
                                </a>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Address') }}</p>
                            @if ($application->company_address)
                                @if ($this->mapLink)
                                    <a href="{{ $this->mapLink }}" target="_blank" rel="noopener" class="leading-relaxed text-accent hover:underline">
                                        {!! nl2br(e($application->company_address)) !!}
                                    </a>
                                @else
                                    <p class="leading-relaxed">{!! nl2br(e($application->company_address)) !!}</p>
                                @endif
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>
                    </div>
                </div>

                <flux:separator variant="subtle" />

                <div class="space-y-6">
                    <flux:heading>{{ __('Contact') }}</flux:heading>

                    <div class="space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</p>
                        @if ($application->contact_name)
                            <p>{{ $application->contact_name }}</p>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</p>
                        @if ($application->contact_email)
                            <a href="mailto:{{ $application->contact_email }}" class="text-accent hover:underline">
                                {{ $application->contact_email }}
                            </a>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</p>
                        @if ($application->contact_phone)
                            <a href="tel:{{ $application->contact_phone }}" class="text-accent hover:underline">
                                {{ $application->contact_phone }}
                            </a>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Source') }}</p>
                        @if ($application->source)
                            <a href="{{ route('redirect', ['url' => $application->source->value()]) }}" target="_blank" rel="noopener" class="break-all text-accent hover:underline">
                                {{ str($application->source)->replaceMatches('#^https?://#i', '')->rtrim('/') }}
                            </a>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </div>
                </div>
            </div>

            <div role="none" class="border-0 bg-zinc-800/5 dark:bg-white/10 max-xl:h-px xl:w-px [print-color-adjust:exact]"></div>

            {{-- Job Description --}}
            <div class="xl:w-3/4 space-y-6">
                <flux:heading>{{ __('Description') }}</flux:heading>
                <div>
                    @if ($application->description)
                        <x-markdown>{{ $application->description }}</x-markdown>
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </div>
            </div>

        </div>
    </flux:callout>

    <x-flyout name="edit-job-listing-modal">
        <flux:heading size="xl" level="1">{{ __('Edit Job Listing') }}</flux:heading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="save">
            <div class="space-y-6">
                <flux:heading size="lg">{{ __('Job Offer') }}</flux:heading>

                <flux:input wire:model="title" :label="__('Title')" />
                <flux:input wire:model="source" :label="__('Source')" type="url" icon="link" />
                <flux:textarea wire:model="description" :label="__('Description')" rows="16" resize="vertical" />
            </div>

            <flux:separator variant="subtle" />

            <div class="space-y-6">
                <flux:heading size="lg">{{ __('Company') }}</flux:heading>

                <div class="grid lg:grid-cols-2 gap-6">
                    <flux:input wire:model="company_name" :label="__('Company name')" icon="building-office" />
                    <flux:input wire:model="company_website" :label="__('Company website')" type="url" icon="link" />
                </div>

                <flux:textarea wire:model="company_address" :label="__('Company address')" rows="3" />
            </div>

            <flux:separator variant="subtle" />

            <div class="space-y-6">
                <flux:heading size="lg">{{ __('Contact') }}</flux:heading>

                <flux:input wire:model="contact_name" :label="__('Contact name')" icon="user" />

                <div class="grid lg:grid-cols-2 gap-6">
                    <flux:input wire:model="contact_email" :label="__('Contact email')" type="email" icon="at-symbol" />
                    <flux:input wire:model="contact_phone" :label="__('Contact phone')" type="tel" icon="phone" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>
</div>
