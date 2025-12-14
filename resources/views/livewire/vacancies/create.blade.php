<?php

use App\Http\Requests\StoreVacancyRequest;
use App\Models\Vacancy;
use App\Enum\SalaryPeriod;
use Livewire\Volt\Component;

new class extends Component {
    public string $title;
    public string $content;

    public function rules(): array
    {
        return (new StoreVacancyRequest())->rules();
    }

    public function create(bool $close = false): void
    {
        $validated = $this->validate();

        Vacancy::create($validated);

        session()->flash('status', __('Vacancy created.'));

        $this->redirectRoute('vacancy.index', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <flux:heading size="xl" level="1">{{ __('Create Vacancy') }}</flux:heading>
    <flux:separator variant="subtle" />

    <form wire:submit="create" class="space-y-6">
        <flux:input wire:model="title" :label="__('Title')" type="text" required autofocus />
        <flux:textarea wire:model="content" :label="__('Content')" />

        <div class="grid grid-cols-3 gap-6">
            <flux:field>
                <flux:label>{{ __('Minimum salary') }}</flux:label>
                <flux:input.group>
                    <flux:input wire:model="salary_min" />
                    <flux:input.group.suffix>€</flux:input.group.suffix>
                </flux:input.group>
                <flux:error name="salary_min" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Maximum salary') }}</flux:label>
                <flux:input.group>
                    <flux:input wire:model="salary_max" />
                    <flux:input.group.suffix>€</flux:input.group.suffix>
                </flux:input.group>
                <flux:error name="salary_max" />
            </flux:field>

            <flux:radio.group wire:model="salary_period" :label="__('Salary period')" :placeholder="__('Select')" variant="segmented">
                @foreach (SalaryPeriod::names() as $value => $label)
                <flux:radio :value="$value" :label="$label" />
                @endforeach
            </flux:radio.group>

            <flux:select wire:model="salary_period" :label="__('Salary period')" :placeholder="__('Select')">
                @foreach (SalaryPeriod::names() as $value => $label)
                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <flux:input wire:model="workplace" :label="__('Workplace')" />
            <flux:input wire:model="weekhours" :label="__('Weekhours')" />
            <flux:input wire:model="location" :label="__('Location')" />
        </div>

        <div>
            <flux:heading size="lg" level="2">{{ __('Company') }}</flux:heading>
            <flux:separator variant="subtle" />
        </div>

        <flux:input wire:model="company" :label="__('Company name')" />

        <flux:textarea wire:model="address" :label="__('Company address')" />

        <div class="grid grid-cols-2 gap-6">
            <flux:input wire:model="contact" :label="__('Company contact')" />
            <flux:input wire:model="email" :label="__('Company email')" />
        </div>

        <flux:input wire:model="website" :label="__('Company website')" />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">
                {{ __('Create') }}
            </flux:button>

            <flux:button variant="subtle" :href="route('vacancies.index')" wire:navigate>
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </form>
</section>
