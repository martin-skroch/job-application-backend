<?php

use App\Models\Vacancy;
use Livewire\Volt\Component;

new class extends Component {
    public Vacancy $vacancy;
}; ?>

<section class="space-y-6">
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ __('Vacancy') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage you vacancy') }}</flux:subheading>
        </div>
        <div>
            <flux:button icon:leading="plus" variant="filled" wire:navigate>
                {{ __('Edit') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    @dump($vacancy)

</section>
