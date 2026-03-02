<?php

use App\Models\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Application $application;

    #[Computed]
    public function analytics(): LengthAwarePaginator
    {
        return $this->application->analytics()->latest('updated_at')->paginate(50);
    }

    public function deleteSession(string $session): void
    {
        $this->application->analytics()->where('session', $session)->delete();
    }
};
?>

<div>
    @if ($this->analytics->isNotEmpty())
        <div>
            @foreach ($this->analytics as $entry)
                <flux:callout wire:key="analytics-{{ $entry->id }}" class="not-first:rounded-t-none not-last:rounded-b-none not-last:border-b-0">
                    <x-slot name="controls">
                        <flux:button
                            icon="trash"
                            variant="ghost"
                            size="sm"
                            wire:click="deleteSession('{{ $entry->session }}')"
                            wire:confirm="{{ __('Delete all entries for this session?') }}"
                        />
                    </x-slot>
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
        <p class="text-zinc-400">{{ __('No analytics entries yet.') }}</p>
    @endif
</div>
