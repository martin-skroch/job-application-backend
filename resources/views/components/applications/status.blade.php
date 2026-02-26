<?php

use App\Enum\ApplicationStatus;
use App\Models\Application;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    public Application $application;

    public ?string $newStatus = null;
    public ?string $statusComment = null;
    public ?string $statusDate = null;
    public int $modalKey = 0;

    public function mount(Application $application): void
    {
        $this->application = $application;
    }

    public function open(): void
    {
        $this->newStatus = null;
        $this->statusComment = null;
        $this->statusDate = null;
        $this->modalKey++;

        Flux::modal('status-modal-' . $this->application->id)->show();
    }

    public function save(): void
    {
        $this->newStatus = $this->newStatus ?: null;

        $this->validate([
            'newStatus' => ['nullable', 'string', 'in:' . implode(',', ApplicationStatus::values())],
            'statusComment' => [$this->newStatus === null ? 'required' : 'nullable', 'string', 'max:5000'],
            'statusDate' => ['nullable', 'date'],
        ]);

        Auth::user()->applications()->withTrashed()->findOrFail($this->application->id)->history()->create([
            'status' => $this->newStatus,
            'comment' => $this->statusComment,
            'created_at' => $this->statusDate ?? now(),
        ]);

        Flux::modal('status-modal-' . $this->application->id)->close();
    }

    public function color(): string
    {
        return match($this->application->status()) {
            ApplicationStatus::Draft    => 'zinc',
            ApplicationStatus::Sent     => 'blue',
            ApplicationStatus::Invited  => 'yellow',
            ApplicationStatus::Accepted => 'green',
            ApplicationStatus::Rejected => 'red',
            default                     => 'zinc',
        };
    }

    public function isDraft() {
        return $this->application->status() === ApplicationStatus::Draft;
    }
};
?>

<div>
    <flux:button wire:click="open" :variant="$this->isDraft() ? null : 'primary'" :color="$this->color()" icon="arrows-right-left" icon:trailing="chevron-down">
        {{ $application->status()?->name ?? __('No status') }}
    </flux:button>

    <flux:modal :name="'status-modal-' . $application->id" class="md:w-96 space-y-6">
        <flux:heading size="lg">{{ __('Change status') }}</flux:heading>

        <form class="space-y-6" wire:submit="save" wire:key="status-form-{{ $modalKey }}">
            <flux:select wire:model="newStatus" :label="__('Status')">
                <flux:select.option value="">{{ __('Comment') }}</flux:select.option>
                @foreach (ApplicationStatus::cases() as $case)
                    <flux:select.option :value="$case->value">{{ __($case->name) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model="statusComment" :label="__('Comment')" :placeholder="__('Optional comment...')" rows="4" />

            <flux:input wire:model="statusDate" :label="__('Date')" type="datetime-local" />

            <div class="flex items-center justify-start gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modal('status-modal-{{ $application->id }}').close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
