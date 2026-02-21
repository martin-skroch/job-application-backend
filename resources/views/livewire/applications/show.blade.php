<?php

use App\Models\Application;
use App\Models\Analytics;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public Application $application;

    public ?string $mapLink = null;

    public function mount()
    {
        $mapQuery = '';

        if (filled($this->application->company_name)) {
            $mapQuery .= $this->application->company_name . '+';
        }

        if (filled($this->application->company_address)) {
            $mapQuery .= str_replace("\n", ",", $this->application->company_address);
        }

        if (filled($mapQuery)) {
            $this->mapLink = 'https://www.google.de/maps/search/' . urlencode($mapQuery);
        }
    }

    public function with(): array
    {
        return [
            'analytics' => $this->application
                ->analytics()
                ->latest('created_at')
                ->paginate(50),
        ];
    }

    // #[\Livewire\Attributes\On('deleteAnalytics')]
    // public function deleteAnalytics(string $id): void
    // {
    //     $this->authorize('view', $this->application);

    //     Analytics::findOrFail($id)->delete();

    //     $this->dispatch('notify', message: 'Analytics-Eintrag gelöscht');
    // }
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

    <flux:callout class="p-4">
        <h3 class="text-xl font-light text-zinc-400 mb-2">
            {{ __('Analytics') }} ({{ __(':count Entries', ['count' => $analytics->total()]) }})
        </h3>

        @php
            $headers = [
                __('Session'),
                __('Time'),
                __('Method'),
                __('User Agent'),
            ];
        @endphp

        @if($analytics->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse block md:table" role="table">
                <thead class="border-b border-zinc-600">
                    <tr class="block md:table-row">
                        @foreach ($headers as $header)
                        <th class="text-sm text-left px-4 py-3 bg-zinc-600">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-600">
                    @foreach($analytics as $entry)
                        @php
                            $entries = [
                                $entry->session => Str::of($entry->session)->limit(20),
                                $entry->created_at->format('d.m.Y H:i:s') => $entry->created_at->diffForHumans(),
                                $entry->method => $entry->method,
                                $entry->user_agent => Str::of($entry->user_agent)->limit(30),
                            ];
                        @endphp
                        <tr class="block md:table-row">
                            @foreach ($entries as $long => $short)
                            <td class="text-sm text-left whitespace-nowrap px-4 py-3 truncate block md:table-cell" title="{{ $long }}">
                                {{ $short }}
                            </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $analytics->links() }}
        </div>
        @else
            <p>Keine Analytics verfügbar</p>
        @endif
    </flux:callout>
</div>
