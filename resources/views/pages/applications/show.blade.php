<?php

use App\Models\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public Application $application;

    #[On('application-saved')]
    public function refreshApplication(): void
    {
        $this->application->refresh();
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
    <livewire:applications.edit-application :application="$application" />

    <flux:separator variant="subtle" />

    {{-- Job Listing --}}
    <livewire:applications.edit-job-listing :application="$application" />

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
