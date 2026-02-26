<?php

use App\Enum\SalaryBehaviors;
use App\Models\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

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
            $mapQuery .= str_replace("\n", ',', $this->application->company_address);
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

    {{-- Header --}}
    <div class="flex max-lg:flex-col lg:items-center gap-6">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ $application->company_name ?: __('No company specified') }}</flux:heading>
            <flux:subheading size="lg">{{ $application->title ?: __('Untitled Application') }}</flux:subheading>
        </div>
        <div class="flex items-center justify-between lg:justify-end gap-6">

            <flux:dropdown>
                <flux:button icon="paper-airplane" icon:trailing="chevron-down">{{ __('Send') }}</flux:button>

                <flux:menu>
                    <livewire:applications.send-preview :application="$application" :is-test="true" />
                    <livewire:applications.send-preview :application="$application" :is-test="false" />
                </flux:menu>
            </flux:dropdown>

            <livewire:applications.status :application="$application" />
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- Application --}}
    <div class="space-y-4">
        <flux:heading size="lg">{{ __('Application') }}</flux:heading>

        <flux:callout class="xl:p-6">
            <div class="flex max-xl:flex-col gap-6 xl:gap-12">
                {{-- Cover Letter --}}
                <div class="xl:w-3/4 space-y-6">
                    <flux:heading>{{ __('Cover Letter') }}</flux:heading>
                    <div>
                        @if ($application->text)
                            <x-markdown>{{ $application->text }}</x-markdown>
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </div>
                </div>

                <div role="none" class="border-0 bg-zinc-800/5 dark:bg-white/10 max-xl:h-px xl:w-px [print-color-adjust:exact]"></div>

                {{-- Profile & Salary --}}
                <div class="xl:w-1/4 space-y-6">
                    <flux:heading>{{ __('Profile') }}</flux:heading>
                    <div class="space-y-4">
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

                            <div class="space-y-1">
                                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Form of Address') }}</p>
                                <p>{{ __($application->form_of_address->name) }}</p>
                            </div>
                        @else
                            <p class="text-zinc-400">{{ __('No profile selected.') }}</p>
                        @endif

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
                </div>

            </div>
        </flux:callout>
    </div>

    {{-- Job Listing --}}
    <div x-data="{ open: false }">
        <div class="flex items-center gap-3 py-4 cursor-pointer select-none" x-on:click="open = !open">
            <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
            <flux:heading size="lg">{{ __('Job Listing') }}</flux:heading>
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
                <div class="xl:w-1/4 space-y-6">
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
    </div>

    <flux:separator variant="subtle" />

    {{-- History --}}
    <div x-data="{ open: false }">
        <div class="flex items-center gap-3 py-4 cursor-pointer select-none" x-on:click="open = !open">
            <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" x-bind:class="{ 'rotate-180': open }" />
            <flux:heading size="lg">{{ __('History') }}</flux:heading>
            <flux:badge size="sm">{{ $application->history()->count() }}</flux:badge>
        </div>

        <div x-show="open" x-transition.opacity>
            <livewire:applications.history :application="$application" />
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- Analytics --}}
    <div x-data="{ open: false }">
        <div class="flex items-center gap-3 py-4 cursor-pointer select-none" x-on:click="open = !open">
            <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform" x-bind:class="{ 'rotate-180': open }" />
            <flux:heading size="lg">{{ __('Analytics') }}</flux:heading>
            <flux:badge size="sm">{{ $this->analytics->total() }}</flux:badge>
        </div>

        <div x-show="open" x-transition.opacity>
            <livewire:applications.analytics :application="$application" />
        </div>
    </div>

</section>
