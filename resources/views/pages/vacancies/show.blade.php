<?php

use App\Models\Vacancy;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Vacancy $vacancy;

    public function mount(): void
    {
        $this->authorize('view', $this->vacancy);
    }

    #[On('vacancy-saved')]
    public function refreshVacancy(): void
    {
        $this->vacancy->refresh();
    }
}; ?>

<section class="space-y-6">

    {{-- Header --}}
    <div class="flex max-lg:flex-col lg:items-center gap-6">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ $vacancy->company_name ?: __('No company specified') }}</flux:heading>
            <flux:subheading size="lg">{{ $vacancy->title ?: __('Untitled Vacancy') }}</flux:subheading>
        </div>
        <div class="flex items-center justify-between lg:justify-end gap-6">
            @if ($vacancy->source)
                <flux:button icon="arrow-top-right-on-square" :href="route('redirect', ['url' => $vacancy->source->value()])" target="_blank" rel="noopener">
                    {{ __('Source') }}
                </flux:button>
            @endif

            <livewire:vacancies.create-application :vacancy="$vacancy" />
        </div>
    </div>

    <flux:separator variant="subtle" />

    <livewire:vacancies.edit :vacancy="$vacancy" :expanded="true" />

</section>
