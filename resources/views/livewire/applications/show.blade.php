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
            'analytics' => $this->application->analytics()
                ->latest('created_at')
                ->paginate(50),
        ];
    }

    #[\Livewire\Attributes\On('deleteAnalytics')]
    public function deleteAnalytics(string $id): void
    {
        $this->authorize('view', $this->application);

        Analytics::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Analytics-Eintrag gelöscht');
    }
};
?>

<section class="space-y-6">
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ $application->title }}</flux:heading>
            <flux:subheading size="lg">{{ $application->company_name }}</flux:subheading>
        </div>
        <div>
            <flux:button variant="ghost" :loading="false" :href="route('applications.index')" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <div class="grid lg:grid-cols-2 gap-6">
        <flux:callout class="p-4">
            <h3 class="text-lg font-medium text-zinc-400">{{ __('Company') }}</h3>

            <flux:separator variant="subtle" class="my-1" />

            <div class="space-y-6">
                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Name') }}</div>
                    <div class="text-lg">{{ $application->company_name }}</div>
                </div>

                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Address') }}</div>
                    <div class="text-lg">{!! nl2br($application->company_address) !!}</div>
                </div>

                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Website') }}</div>
                    <div class="text-lg">
                        @if($application->company_website)
                        <a href="{{ route('redirect', ['url' => (string) $application->company_website]) }}" target="_blank" rel="noopener" class="text-accent hover:underline">
                            {{ $application->company_website }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </flux:callout>

        <flux:callout class="p-4">
            <h3 class="text-lg font-medium text-zinc-400">{{ __('Contact') }}</h3>

            <flux:separator variant="subtle" class="my-1" />

            <div class="space-y-6">
                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Contact name') }}</div>
                    <div class="text-lg">{{ $application->contact_name }}</div>
                </div>

                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Contact email') }}</div>
                    <div class="text-lg">
                        @if ($application->contact_email)
                        <a href="mailto:{{ $application->contact_email }}" class="text-accent hover:underline">
                            {{ $application->contact_email }}
                        </a>
                        @endif
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="text-sm font-medium text-zinc-400">{{ __('Contact phone') }}</div>
                    <div class="text-lg">
                        @if ($application->contact_phone)
                        <a href="tel:{{ $application->contact_phone }}" class="text-accent hover:underline">
                            {{ $application->contact_phone }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </flux:callout>
    </div>

    @if($application->text)
    <flux:callout class="p-4">
        <h3 class="text-lg font-medium text-zinc-400">{{ __('Text') }}</h3>

        <flux:separator variant="subtle" class="my-1" />

        {{ $application->text }}
    </flux:callout>
    @endif

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
                __('Time'),
                __('Method'),
                __('IP'),
                __('Session'),
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
                                $entry->created_at->format('d.m.Y H:i:s'),
                                $entry->method,
                                $entry->ip,
                                $entry->session,
                                $entry->user_agent,
                            ];
                        @endphp
                        <tr class="block md:table-row">
                            @foreach ($entries as $entry)
                            <td class="text-sm text-left whitespace-nowrap px-4 py-3 truncate block md:table-cell">
                                {{ $entry }}
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
