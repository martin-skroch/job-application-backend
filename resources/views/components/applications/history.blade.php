<?php

use App\Enum\ApplicationStatus;
use App\Models\Application;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Application $application;

    public ?string $editingId = null;
    public ?string $editStatus = null;
    public ?string $editComment = null;
    public ?string $editDate = null;
    public int $modalKey = 0;

    #[Computed]
    public function history(): Collection
    {
        return $this->application->history()->get();
    }

    public function edit(string $id): void
    {
        $entry = $this->application->history()->findOrFail($id);

        $this->editingId = $id;
        $this->editStatus = $entry->status?->value ?? '';
        $this->editComment = $entry->comment;
        $this->editDate = $entry->created_at->format('Y-m-d\TH:i');
        $this->modalKey++;

        Flux::modal('history-edit-' . $this->application->id)->show();
    }

    public function update(): void
    {
        $this->authorize('update', $this->application);

        $this->editStatus = $this->editStatus ?: null;

        $this->validate([
            'editStatus' => ['nullable', 'string', 'in:' . implode(',', ApplicationStatus::values())],
            'editComment' => [$this->editStatus === null ? 'required' : 'nullable', 'string', 'max:5000'],
            'editDate' => ['required', 'date'],
        ]);

        $this->application->history()->findOrFail($this->editingId)->update([
            'status' => $this->editStatus,
            'comment' => $this->editComment,
            'created_at' => $this->editDate,
        ]);

        $this->editingId = null;
        unset($this->history);

        Flux::modal('history-edit-' . $this->application->id)->close();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;

        Flux::modal('history-edit-' . $this->application->id)->close();
    }

    public function delete(string $id): void
    {
        $this->authorize('update', $this->application);

        $this->application->history()->findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->editingId = null;
        }

        unset($this->history);
    }
}; ?>

<div>
    @if ($this->history->isNotEmpty())
        <div>
            @foreach ($this->history as $entry)

                @php
                    $colors = match($entry->status) {
                        ApplicationStatus::Draft    => 'zinc',
                        ApplicationStatus::Sent     => 'blue',
                        ApplicationStatus::Invited  => 'yellow',
                        ApplicationStatus::Accepted => 'green',
                        ApplicationStatus::Rejected => 'red',
                        default                     => 'zinc',
                    };
                @endphp

                <flux:callout x-data="{ open: false }" wire:key="history-{{ $entry->id }}" class="not-first:rounded-t-none not-last:rounded-b-none not-last:border-b-0">
                    <div @if ($entry->comment) x-on:click="open = !open" @endif class="flex items-center gap-4{{ $entry->comment ? ' cursor-pointer select-none' : '' }}">
                        @if ($entry->comment)
                            <flux:icon icon="chevron-down" class="size-4 text-zinc-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                        @else
                            <span class="size-4"></span>
                        @endif

                        <flux:badge size="sm" color="{{ $colors }}">{{ __($entry->status?->name ?? 'Comment') }}</flux:badge>

                        <div class="flex items-center gap-2 ms-auto">
                            <p class="text-sm text-zinc-400" title="{{ $entry->created_at->format('d.m.Y H:i') }}">
                                {{ $entry->created_at?->diffForHumans() }}
                            </p>

                            <flux:button
                                variant="ghost"
                                icon="pencil-square"
                                size="sm"
                                wire:click.stop="edit('{{ $entry->id }}')"
                                :title="__('Edit')"
                            />

                            <flux:button
                                variant="ghost"
                                icon="trash"
                                size="sm"
                                wire:click.stop="delete('{{ $entry->id }}')"
                                wire:confirm="{{ __('Delete this history entry?') }}"
                                :title="__('Delete')"
                            />
                        </div>
                    </div>

                    @if ($entry->comment)
                    <div x-if="true" x-show="open" x-transition.opacity class="ms-8 mt-3">
                        <x-markdown>{{ $entry->comment }}</x-markdown>
                    </div>
                    @endif
                </flux:callout>
            @endforeach
        </div>
    @else
        <p class="text-zinc-400">{{ __('No history entries yet.') }}</p>
    @endif

    @teleport('body')
    <x-flyout name="experience-files-modal" :name="'history-edit-' . $application->id" class="space-y-6">
        <flux:heading size="lg">{{ __('Edit history entry') }}</flux:heading>

        <form class="space-y-6" wire:submit="update" wire:key="history-edit-form-{{ $modalKey }}">
            <flux:select wire:model="editStatus" :label="__('Status')">
                <flux:select.option value="">{{ __('Comment') }}</flux:select.option>
                @foreach (ApplicationStatus::cases() as $case)
                    <flux:select.option :value="$case->value">{{ __($case->name) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model="editComment" :label="__('Comment')" rows="16" />

            <flux:input wire:model="editDate" :label="__('Date')" type="datetime-local" />

            <div class="flex items-center justify-start gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" type="button" wire:click="cancelEdit">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>
    @endteleport
</div>
