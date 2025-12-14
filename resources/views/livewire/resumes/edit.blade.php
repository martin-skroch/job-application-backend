<?php

use App\Http\Requests\UpdateResumeRequest;
use App\Models\Resume;
use Livewire\Volt\Component;

new class extends Component {
    public string $title = '';

    public Resume $resume;

    public function mount(Resume $resume)
    {
        $this->title = $resume->title ?? '';
    }

    public function rules()
    {
        return (new UpdateResumeRequest())->rules();
    }

    public function save(bool $close = false): void
    {
        $validated = $this->validate();

        $this->resume->fill($validated);
        $this->resume->save();

        session()->flash('status', __('Resume updated.'));

        if ($close) {
            $this->close();
            return;
        }

        $this->dispatch('resume-saved');
    }

    public function close(): void {
        $this->redirectRoute('resumes.index', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <flux:heading size="xl" level="1">{{ __('Edit Resume') }}</flux:heading>
    <flux:separator variant="subtle" />

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="title" :label="__('Title')" type="text" required />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="button" wire:click="save(true)">
                {{ __('Save & Close') }}
            </flux:button>

            <flux:button variant="primary" type="submit">
                {{ __('Save') }}
            </flux:button>

            <x-action-message on="resume-saved">
                {{ __('Saved.') }}
            </x-action-message>

            <flux:button variant="ghost" type="button" wire:click="close" class="ms-auto">
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</div>
