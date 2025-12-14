<?php

use App\Http\Requests\StoreResumeRequest;
use App\Models\Resume;
use Livewire\Volt\Component;

new class extends Component {
    public string $title;

    public function rules(): array
    {
        return (new StoreResumeRequest())->rules();
    }

    public function create(bool $close = false): void
    {
        $validated = $this->validate();

        Resume::create($validated);

        session()->flash('status', __('Resume created.'));

        $this->redirectRoute('resumes.index', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <flux:heading size="xl" level="1">{{ __('Create Resume') }}</flux:heading>
    <flux:separator variant="subtle" />

    <form wire:submit="create" class="space-y-6">
        <flux:input wire:model="title" :label="__('Title')" type="text" required autofocus />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">
                {{ __('Create') }}
            </flux:button>

            <flux:button variant="subtle" :href="route('resumes.index')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</section>
