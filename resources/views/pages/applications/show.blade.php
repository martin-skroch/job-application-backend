<?php

use App\Models\Application;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;

    public Application $application;

    #[Computed]
    public function mapLink(): string|null
    {
        $mapQuery = '';

        if (filled($this->application->company_name)) {
            $mapQuery .= $this->application->company_name . '+';
        }

        if (filled($this->application->company_address)) {
            $mapQuery .= str_replace("\n", ",", $this->application->company_address);
        }

        if (blank($mapQuery)) {
            return null;
        }

        return 'https://www.google.de/maps/search/' . urlencode($mapQuery);
    }

    #[Computed]
    public function analytics(): LengthAwarePaginator
    {
        return $this->application->analytics()->latest('updated_at')->paginate(50);
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ $application->title }}</flux:heading>
        </div>
        <div>
            <flux:button variant="ghost" :loading="false" :href="route('applications.index')" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <div class="grid xl:grid-cols-3 gap-6">

        <div class="xl:col-span-1 space-y-6">

            <flux:callout>
                <div class="space-y-6">
                    <div class="space-y-1">
                        <flux:heading>{{ __('Source') }}</flux:heading>

                        @if ($application->source)
                            <a href="{{ route('redirect', ['url' => $application->source?->value()]) }}" target="_blank" rel="noopener" class="inline-flex text-accent hover:underline">
                                {{ $application->source }}
                            </a>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <flux:heading>{{ __('Address') }}</flux:heading>

                        @if ($application->company_address)
                            <div>
                                @if ($this->mapLink)
                                <a href="{{ $this->mapLink }}" target="_blank" rel="noopener" class="inline-flex text-accent hover:underline">
                                @endif

                                    {!! nl2br($application->company_address) !!}

                                @if ($this->mapLink)
                                </a>
                                @endif
                            </div>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <flux:heading>{{ __('Website') }}</flux:heading>

                        @if ($application->company_website)
                            <div>
                                <a href="{{ route('redirect', ['url' => (string) $application->company_website]) }}" target="_blank" rel="noopener" class="text-accent hover:underline">
                                    {{ str($application->company_website)->replaceMatches('#https?://#i', '') }}
                                </a>
                            </div>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>
                </div>
            </flux:callout>

            <flux:callout>
                <div class="space-y-6">
                    <div class="space-y-1">
                        <flux:heading>{{ __('Company') }}</flux:heading>

                        @if ($application->company_name)
                            <div>{{ $application->company_name }}</div>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <flux:heading>{{ __('Address') }}</flux:heading>

                        @if ($application->company_address)
                            <div>
                                @if ($this->mapLink)
                                <a href="{{ $this->mapLink }}" target="_blank" rel="noopener" class="inline-flex text-accent hover:underline">
                                @endif

                                    {!! nl2br($application->company_address) !!}

                                @if ($this->mapLink)
                                </a>
                                @endif
                            </div>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <flux:heading>{{ __('Website') }}</flux:heading>

                        @if ($application->company_website)
                            <div>
                                <a href="{{ route('redirect', ['url' => (string) $application->company_website]) }}" target="_blank" rel="noopener" class="text-accent hover:underline">
                                    {{ str($application->company_website)->replaceMatches('#https?://#i', '') }}
                                </a>
                            </div>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>
                </div>
            </flux:callout>

            <flux:callout>
                <div class="space-y-6">
                    <div class="space-y-1">
                        <flux:heading>{{ __('Contact') }}</flux:heading>

                        @if ($application->contact_name)
                            <div>{{ $application->contact_name }}</div>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <flux:heading>{{ __('Email') }}</flux:heading>

                        @if ($application->contact_email)
                            <div>
                                <a href="mailto:{{ $application->contact_email }}" class="inline-flex text-accent hover:underline">
                                    {{ $application->contact_email }}
                                </a>
                            </div>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <flux:heading>{{ __('Phone') }}</flux:heading>

                        @if ($application->contact_phone)
                            <div>
                                <a href="tel:{{ $application->contact_phone }}" class="inline-flex text-accent hover:underline">
                                    {{ $application->contact_phone }}
                                </a>
                            </div>
                        @else
                            <div class="text-zinc-500">{!! __('-') !!}</div>
                        @endif
                    </div>
                </div>
            </flux:callout>

        </div>

        <div class="xl:col-span-2 space-y-6">

            <flux:callout>
                <flux:heading>{{ __('Greeting') }}</flux:heading>
                <x-markdown>{{ $application->greeting }}</x-markdown>
            </flux:callout>

            <flux:callout>
                <flux:heading>{{ __('Text') }}</flux:heading>
                <x-markdown>{{ $application->text }}</x-markdown>
            </flux:callout>

            <flux:callout>
                <flux:heading>{{ __('Text') }}</flux:heading>
                <x-markdown>{{ $application->notes }}</x-markdown>
            </flux:callout>

        </div>

    </div>

    <h3 class="text-xl font-light text-zinc-400">
        {{ __('Analytics') }} ({{ __(':count Entries', ['count' => $this->analytics->total()]) }})
    </h3>

    @if($this->analytics->count() > 0)
    <div>
        @foreach($this->analytics as $entry)
            <flux:callout wire:key="analytics-{{ $entry->id }}" class="not-first:rounded-t-none not-last:rounded-b-none not-last:border-b-0">
                <div class="grid grid-cols-2 lg:grid-cols-6 gap-6">
                    <div class="col-span-2 lg:col-span-4 space-y-3">
                        <div>
                            <flux:heading class="text-current/50">{{ __('Session') }}</flux:heading>
                            <p class="text-sm md:truncate break-all">{{ $entry->session }}</p>
                        </div>
                        <div>
                            <flux:heading class="text-current/50">{{ __('User Agent') }}</flux:heading>
                            <p class="text-sm md:truncate" title="{{ $entry->user_agent }}">{{ $entry->user_agent }}</p>
                        </div>
                    </div>

                    <div class="col-span-1 space-y-3">
                        <div>
                            <flux:heading class="text-current/50">{{ __('Count') }}</flux:heading>
                            <p class="text-sm">{{ $entry->count ?? 1 }}</p>
                        </div>

                        <div>
                            <flux:heading class="text-current/50">{{ __('Method') }}</flux:heading>
                            <p class="text-sm">{{ $entry->method }}</p>
                        </div>
                    </div>

                    <div class="col-span-1 space-y-3">
                        <div>
                            <flux:heading class="text-current/50">{{ __('Last Visit') }}</flux:heading>
                            <p class="text-sm" title="{{ $entry->updated_at?->format('d.m.Y H:i:s') }}">{{ $entry->updated_at?->diffForHumans() ?? '-' }}</p>
                        </div>
                        <div>
                            <flux:heading class="text-current/50">{{ __('First Visit') }}</flux:heading>
                            <p class="text-sm" title="{{ $entry->created_at->format('d.m.Y H:i:s') }}">{{ $entry->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </flux:callout>
        @endforeach
    </div>

    <div>
        {{ $this->analytics->links() }}
    </div>
    @else
        <p class="text-zinc-400">{{ __('Keine Analytics verfügbar') }}</p>
    @endif
</div>
