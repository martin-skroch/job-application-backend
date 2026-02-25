<?php

use App\Actions\SendApplication;
use App\Enum\ApplicationStatus;
use App\Enum\SalaryBehaviors;
use App\Models\Application;
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

        if (! $this->application->profile) {
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

        if (! empty($this->sendIssues)) {
            return;
        }

        $this->sendApplication->handle($this->application, setStatus: true);
    }

    public function sendTestEmail(): void
    {
        $this->authorize('update', $this->application);

        if (! empty($this->testEmailIssues)) {
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
            $mapQuery .= str_replace("\n", ',', $this->application->company_address);
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

<section class="space-y-8">

    {{-- Header --}}
    <div class="flex items-start gap-4">
        <div class="grow min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
                <flux:heading size="xl" level="1">
                    {{ $application->title ?: __('Untitled Application') }}
                </flux:heading>
                <flux:badge size="sm" color="{{ match($application->status()) {
                    ApplicationStatus::Draft    => 'zinc',
                    ApplicationStatus::Sent     => 'blue',
                    ApplicationStatus::Invited  => 'yellow',
                    ApplicationStatus::Accepted => 'green',
                    ApplicationStatus::Rejected => 'red',
                    default                     => 'zinc',
                } }}">{{ __($application->status()?->name ?? 'No status') }}</flux:badge>
            </div>
            @if ($application->company_name)
                <flux:subheading size="lg" class="mt-1">{{ $application->company_name }}</flux:subheading>
            @endif
        </div>

        <div class="flex items-center gap-2 shrink-0">
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

            <flux:button variant="ghost" icon="arrow-left" :href="route('applications.index')" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- Main grid --}}
    <div class="grid xl:grid-cols-3 gap-6">

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Overview --}}
            <flux:callout>
                <flux:heading>{{ __('Overview') }}</flux:heading>
                <div class="mt-4 space-y-4 text-sm">
                    <div class="space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Source') }}</p>
                        @if ($application->source)
                            <a href="{{ route('redirect', ['url' => $application->source->value()]) }}" target="_blank" rel="noopener" class="break-all text-accent hover:underline">
                                {{ str($application->source)->replaceMatches('#https?://#i', '') }}
                            </a>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Public URL') }}</p>
                        @if ($application->isPublic())
                            <a href="{{ config('app.frontend_url') . '/' . $application->public_id }}" target="_blank" rel="noopener" class="font-mono text-accent hover:underline">
                                {{ $application->public_id }}
                            </a>
                        @else
                            <span class="text-zinc-400">{{ __('Not public') }}</span>
                        @endif
                    </div>
                </div>
            </flux:callout>

            {{-- Company --}}
            <flux:callout>
                <flux:heading>{{ __('Company') }}</flux:heading>
                <div class="mt-4 space-y-4 text-sm">
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
                                {{ str($application->company_website)->replaceMatches('#https?://#i', '') }}
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
            </flux:callout>

            {{-- Contact --}}
            <flux:callout>
                <flux:heading>{{ __('Contact') }}</flux:heading>
                <div class="mt-4 space-y-4 text-sm">
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
                </div>
            </flux:callout>

            {{-- Profile & Salary --}}
            <flux:callout>
                <flux:heading>{{ __('Profile') }}</flux:heading>
                <div class="mt-4 space-y-4 text-sm">
                    @if ($application->profile)
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</p>
                            <p>{{ $application->profile->name }}</p>
                        </div>

                        @if ($application->profile->title)
                            <div class="space-y-1">
                                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Title') }}</p>
                                <p>{{ $application->profile->title }}</p>
                            </div>
                        @endif

                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</p>
                            @if ($application->profile->email)
                                <a href="mailto:{{ $application->profile->email }}" class="text-accent hover:underline">
                                    {{ $application->profile->email }}
                                </a>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Salary') }}</p>
                            @if ($application->salary_behavior === SalaryBehaviors::Hidden)
                                <span class="text-zinc-400">{{ __('Hidden') }}</span>
                            @elseif ($application->salary_behavior === SalaryBehaviors::Override && $application->salary_desire)
                                <p>{{ number_format($application->salary_desire, 0, ',', '.') }} €</p>
                            @elseif ($application->salary_behavior === SalaryBehaviors::Inherit && $application->profile->salary_desire)
                                <p>{{ number_format($application->profile->salary_desire, 0, ',', '.') }} € <span class="text-zinc-400">({{ __('from profile') }})</span></p>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>
                    @else
                        <p class="text-zinc-400">{{ __('No profile selected.') }}</p>
                    @endif
                </div>
            </flux:callout>

            {{-- Dates --}}
            <flux:callout>
                <flux:heading>{{ __('Dates') }}</flux:heading>
                <div class="mt-4 space-y-4 text-sm">
                    <div class="space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Created') }}</p>
                        <p title="{{ $application->created_at->format('d.m.Y H:i') }}">
                            {{ $application->created_at->format('d.m.Y') }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Updated') }}</p>
                        <p title="{{ $application->created_at->format('d.m.Y H:i') }}">
                            {{ $application->updated_at->format('d.m.Y') }}
                        </p>
                    </div>
                </div>
            </flux:callout>

        </div>

        {{-- Cover letter --}}
        <div class="xl:col-span-2">
            <flux:callout class="h-full">
                <div class="space-y-6">
                    <div class="space-y-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Greeting') }}</p>
                        <x-markdown>{{ $application->greeting }}</x-markdown>
                    </div>

                    <flux:separator variant="subtle" />

                    <div class="space-y-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Cover Letter') }}</p>
                        <x-markdown>{{ $application->text }}</x-markdown>
                    </div>
                </div>
            </flux:callout>
        </div>

    </div>

    {{-- History --}}
    <div class="space-y-4">
        <flux:heading size="lg">{{ __('History') }}</flux:heading>

        @if ($this->history->isNotEmpty())
            <div>
                @foreach ($this->history as $entry)
                    <flux:callout wire:key="history-{{ $entry->id }}" class="not-first:rounded-t-none not-last:rounded-b-none not-last:border-b-0">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 space-y-1">
                                <flux:badge size="sm" color="{{ match($entry->status) {
                                    ApplicationStatus::Draft    => 'zinc',
                                    ApplicationStatus::Sent     => 'blue',
                                    ApplicationStatus::Invited  => 'yellow',
                                    ApplicationStatus::Accepted => 'green',
                                    ApplicationStatus::Rejected => 'red',
                                    default                     => 'zinc',
                                } }}">{{ __($entry->status?->name ?? 'Comment') }}</flux:badge>

                                @if ($entry->comment)
                                    <x-markdown>{{ $entry->comment }}</x-markdown>
                                @endif
                            </div>

                            <p class="shrink-0 text-sm text-zinc-400" title="{{ $entry->created_at->format('d.m.Y H:i') }}">
                                {{ $entry->created_at?->diffForHumans() }}
                            </p>
                        </div>
                    </flux:callout>
                @endforeach
            </div>
        @else
            <p class="text-zinc-400">{{ __('No history entries yet.') }}</p>
        @endif
    </div>

    {{-- Analytics --}}
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <flux:heading size="lg">{{ __('Analytics') }}</flux:heading>
            <flux:badge size="sm">{{ $this->analytics->total() }}</flux:badge>
        </div>

        @if ($this->analytics->count() > 0)
            <div>
                @foreach ($this->analytics as $entry)
                    <flux:callout wire:key="analytics-{{ $entry->id }}" class="not-first:rounded-t-none not-last:rounded-b-none not-last:border-b-0">
                        <div class="grid grid-cols-2 gap-6 text-sm lg:grid-cols-6">
                            <div class="col-span-2 space-y-3 lg:col-span-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Session') }}</p>
                                    <p class="break-all md:truncate">{{ $entry->session }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('User Agent') }}</p>
                                    <p class="md:truncate" title="{{ $entry->user_agent }}">{{ $entry->user_agent }}</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Count') }}</p>
                                    <p>{{ $entry->count ?? 1 }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Method') }}</p>
                                    <p>{{ $entry->method }}</p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Last Visit') }}</p>
                                    <p title="{{ $entry->updated_at?->format('d.m.Y H:i:s') }}">{{ $entry->updated_at?->diffForHumans() ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('First Visit') }}</p>
                                    <p title="{{ $entry->created_at->format('d.m.Y H:i:s') }}">{{ $entry->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </flux:callout>
                @endforeach
            </div>

            <div>{{ $this->analytics->links() }}</div>
        @else
            <p class="text-zinc-400">{{ __('Keine Analytics verfügbar') }}</p>
        @endif
    </div>

</section>
