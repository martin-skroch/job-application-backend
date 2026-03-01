<?php

use App\Enum\ApplicationStatus;
use App\Models\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $search = '';

    #[Computed]
    public function results(): Collection
    {
        if (strlen($this->search) < 2) {
            return collect();
        }

        $term = '%' . $this->search . '%';

        return Auth::user()->applications()
            ->withTrashed()
            ->with('latestStatusEntry')
            ->where(function ($q) use ($term): void {
                $q->where('title', 'like', $term)
                    ->orWhere('company_name', 'like', $term)
                    ->orWhere('company_website', 'like', $term)
                    ->orWhere('company_address', 'like', $term)
                    ->orWhere('contact_name', 'like', $term)
                    ->orWhere('contact_email', 'like', $term)
                    ->orWhere('contact_phone', 'like', $term)
                    ->orWhere('source', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('text', 'like', $term);
            })
            ->limit(8)
            ->get();
    }

    public function resetSearch(): void
    {
        $this->search = '';
    }

    public function statusColor(Application $application): string
    {
        return match ($application->status()) {
            ApplicationStatus::Bookmarked => 'orange',
            ApplicationStatus::Sent       => 'blue',
            ApplicationStatus::Invited    => 'yellow',
            ApplicationStatus::Accepted   => 'green',
            ApplicationStatus::Rejected   => 'red',
            default                       => 'zinc',
        };
    }
};
?>

<div>
    <flux:modal.trigger name="application-search" shortcut="cmd.k">
        <flux:input as="button" :placeholder="__('Search...')" icon="magnifying-glass" kbd="⌘K" />
    </flux:modal.trigger>

    @teleport('body')
    <flux:modal name="application-search" class="w-[90vw]" wire:close="resetSearch" :closable="false">
        <div class="space-y-4">
            <flux:input wire:model.live.debounce.300ms="search" :placeholder="__('Search applications...')" icon="magnifying-glass" autocomplete="off" autofocus clearable />

            @if (strlen($this->search) >= 2)
                @if ($this->results->isNotEmpty())
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->results as $application)
                            @php $color = $this->statusColor($application); @endphp
                            @if (!$application->isArchived())
                                <a href="{{ route('applications.show', $application) }}" wire:navigate wire:key="sr-{{ $application->id }}" class="flex items-center justify-between gap-4 py-3 px-2 hover:bg-zinc-100 dark:hover:bg-zinc-700">
                                    <div>
                                        <p class="font-semibold text-sm">{{ $application->company_name ?: __('No company specified') }}</p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $application->title ?: __('Untitled Application') }}</p>
                                    </div>
                                    <flux:badge size="sm" :color="$color">{{ __($application->status()?->name ?? 'Draft') }}</flux:badge>
                                </a>
                            @else
                                <div wire:key="sr-{{ $application->id }}" class="flex items-center justify-between gap-4 py-3 px-2">
                                    <div>
                                        <p class="font-semibold text-sm text-zinc-400 dark:text-zinc-500">{{ $application->company_name ?: __('No company specified') }}</p>
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $application->title ?: __('Untitled Application') }}</p>
                                    </div>
                                    <flux:badge size="sm" color="zinc">{{ __('Archived') }}</flux:badge>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 py-2">{{ __('No applications found.') }}</p>
                @endif
            @endif
        </div>
    </flux:modal>
    @endteleport
</div>
