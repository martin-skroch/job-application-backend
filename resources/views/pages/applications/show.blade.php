<?php

use App\Actions\SendApplication;
use App\Enum\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public Application $application;

    private SendApplication $sendApplication;

    public function boot(SendApplication $sendApplication): void
    {
        $this->sendApplication = $sendApplication;
    }

    #[Computed]
    public function sendIssues(): array
    {
        $issues = [];

        if ($this->application->status() === ApplicationStatus::Sent) {
            $issues[] = __('Application has already been sent.');
        }

        if (blank($this->application->contact_email)) {
            $issues[] = __('Contact email is missing.');
        }

        if (!$this->application->profile) {
            $issues[] = __('No profile selected.');
        }

        return $issues;
    }

    #[Computed]
    public function testEmailIssues(): array
    {
        $issues = $this->sendIssues;

        if (blank($this->application->profile?->email)) {
            $issues[] = __('Profile has no email address.');
        }

        return $issues;
    }

    public function sendApplication(): void
    {
        $this->authorize('update', $this->application);

        if (!empty($this->sendIssues)) {
            return;
        }

        $this->sendApplication->handle($this->application, setStatus: true);
    }

    public function sendTestEmail(): void
    {
        $this->authorize('update', $this->application);

        if (!empty($this->testEmailIssues)) {
            return;
        }

        $this->sendApplication->handle($this->application, setStatus: false);
    }

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
    public function history(): Collection
    {
        return $this->application->history()->get();
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
        <div class="flex items-center gap-2">
            @if ($this->sendIssues)
                <flux:tooltip :content="implode(' ', $this->sendIssues)">
                    <flux:button icon="paper-airplane" disabled>
                        {{ __('Send') }}
                    </flux:button>
                </flux:tooltip>
            @else
                <flux:button
                    icon="paper-airplane"
                    wire:click="sendApplication"
                    wire:confirm="{{ __('Send the application to :email now?', ['email' => $application->contact_email]) }}"
                >
                    {{ __('Send') }}
                </flux:button>
            @endif

            @if ($this->testEmailIssues)
                <flux:tooltip :content="implode(' ', $this->testEmailIssues)">
                    <flux:button variant="ghost" icon="beaker" disabled>
                        {{ __('Test email') }}
                    </flux:button>
                </flux:tooltip>
            @else
                <flux:button
                    variant="ghost"
                    icon="beaker"
                    wire:click="sendTestEmail"
                    wire:confirm="{{ __('Send a test email to :email?', ['email' => $application->profile->email]) }}"
                >
                    {{ __('Test email') }}
                </flux:button>
            @endif

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


        </div>

    </div>

    <h3 class="text-xl font-light text-zinc-400">{{ __('History') }}</h3>

    @if ($this->history->isNotEmpty())
        <div>
            @foreach ($this->history as $entry)
            <flux:callout wire:key="history-{{ $entry->id }}" class="not-first:rounded-t-none not-last:rounded-b-none not-last:border-b-0">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1 min-w-0">
                        <flux:badge size="sm" color="{{ match($entry->status) {
                                ApplicationStatus::Draft            => 'zinc',
                                ApplicationStatus::Sent             => 'blue',
                                ApplicationStatus::Invited          => 'yellow',
                                ApplicationStatus::Accepted         => 'green',
                                ApplicationStatus::Rejected         => 'red',
                                default                             => 'zinc'
                            } }}">{{ __($entry->status?->name ?? 'Comment') }}</flux:badge>

                        @if ($entry->comment)
                            <x-markdown>{{ $entry->comment }}</x-markdown>
                        @endif
                    </div>

                    <p class="text-sm text-zinc-400 shrink-0" title="{{ $entry->created_at->format('d.m.Y H:i') }}">
                        {{ $entry->created_at?->diffForHumans() }}
                    </p>
                </div>
            </flux:callout>
            @endforeach
        </div>
    @else
        <p class="text-zinc-400">{{ __('No history entries yet.') }}</p>
    @endif

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
