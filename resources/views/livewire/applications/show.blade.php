<?php

use App\Models\Application;
use App\Models\Analytics;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public Application $application;

    public function with(): array
    {
        return [
            'analytics' => $this->application
                ->analytics()
                ->latest('created_at')
                ->paginate(50),
        ];
    }

    #[\Livewire\Attributes\On('deleteAnalytics')]
    // public function deleteAnalytics(string $id): void
    // {
    //     $this->authorize('view', $this->application);

    //     Analytics::findOrFail($id)->delete();

    //     $this->dispatch('notify', message: 'Analytics-Eintrag gelöscht');
    // }
};
?>

<section class="space-y-6">
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ $application->title }}</flux:heading>
            @if ($application->source)
            <flux:subheading class="flex items-center gap-1.5 text-base!">
                <flux:icon class="size-3.5" name="link" /> {{ __('Source') }}:
                <a href="{{ route('redirect', ['url' => (string) $application->source]) }}" target="_blank" rel="noopener" class="hover:text-accent hover:underline inline-flex items-center gap-1">
                    {{ $application->source }}
                </a>
            </flux:subheading>
            @endif
        </div>
        <div>
            <flux:button variant="ghost" :loading="false" :href="route('applications.index')" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <flux:callout class="p-4">
        <h3 class="text-lg font-medium text-zinc-400">{{ __('Text') }}</h3>

        <flux:separator variant="subtle" class="my-1" />

        {{ $application->text }}
    </flux:callout>

    <div class="grid lg:grid-cols-2 gap-6">
        <flux:callout class="p-4">
            <h3 class="text-lg font-medium text-zinc-400">{{ __('Company') }}</h3>

            <flux:separator variant="subtle" class="my-1" />

            <div class="space-y-6">
                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Name') }}</div>
                    <div class="text-lg">{{ $application->company_name }}</div>
                </div>

                @if ($application->company_name && $application->company_address)
                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Address') }}</div>
                    <div class="text-lg">
                        @php  $mapLink = $application->company_name . '+' . str_replace("\n", ",", $application->company_address); @endphp
                        <a href="https://www.google.de/maps/search/{{ $mapLink }}/" target="_blank" rel="noopener" class="inline-flex text-accent hover:underline">
                            {!! nl2br($application->company_address) !!}
                        </a>
                    </div>
                </div>
                @endif

                @if($application->company_website)
                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Website') }}</div>
                    <div class="text-lg">
                        <a href="{{ route('redirect', ['url' => (string) $application->company_website]) }}" target="_blank" rel="noopener" class="text-accent hover:underline">
                            {{ $application->company_website }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </flux:callout>

        <flux:callout class="p-4">
            <h3 class="text-lg font-medium text-zinc-400">{{ __('Contact') }}</h3>

            <flux:separator variant="subtle" class="my-1" />

            <div class="space-y-6">
                @if ($application->contact_name)
                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Name') }}</div>
                    <div class="text-lg">{{ $application->contact_name }}</div>
                </div>
                @endif

                @if ($application->contact_email)
                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Email') }}</div>
                    <div class="text-lg">
                        @if ($application->contact_email)
                        <a href="mailto:{{ $application->contact_email }}" class="text-accent hover:underline">
                            {{ $application->contact_email }}
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                @if ($application->contact_phone)
                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Phone') }}</div>
                    <div class="text-lg">
                        @if ($application->contact_phone)
                        <a href="tel:{{ $application->contact_phone }}" class="text-accent hover:underline">
                            {{ $application->contact_phone }}
                        </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </flux:callout>
    </div>

    @if($application->notes)
    <flux:callout class="p-4">
        <h3 class="text-lg font-medium text-zinc-400">{{ __('Notes') }}</h3>

        <flux:separator variant="subtle" class="my-1" />

        {{ $application->notes }}
    </flux:callout>
    @endif

    <flux:callout class="p-4">
        <h3 class="text-lg font-medium text-zinc-400">
            {{ __('Analytics') }} ({{ __(':count Entries', ['count' => $analytics->total()]) }})
        </h3>

        <flux:separator variant="subtle" class="my-1" />

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
