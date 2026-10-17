<?php

use App\Actions\CreateApplicationFromVacancy;
use App\Enum\FormOfAddress;
use App\Enum\SalaryBehaviors;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Vacancy;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public Vacancy $vacancy;

    public string $size = 'base';

    public ?string $profile_id = null;

    public ?FormOfAddress $form_of_address = FormOfAddress::Formal;

    public ?SalaryBehaviors $salary_behavior = SalaryBehaviors::Hidden;

    public ?int $salary_desire = null;

    public ?string $earliest_entry_date = null;

    public ?string $text = null;

    public function mount(): void
    {
        $this->authorize('view', $this->vacancy);
    }

    public function open(): void
    {
        $this->authorize('create', Application::class);
        $this->authorize('update', $this->vacancy);

        $this->reset('profile_id', 'form_of_address', 'salary_behavior', 'salary_desire', 'earliest_entry_date', 'text');
        $this->resetErrorBag();

        Flux::modal('create-application-modal-'.$this->vacancy->id)->show();
    }

    public function updatedProfileId(): void
    {
        $profile = Auth::user()->profiles()->find($this->profile_id);

        $this->text = $profile?->cover_letter;
    }

    public function save(CreateApplicationFromVacancy $createApplicationFromVacancy): void
    {
        $this->authorize('update', $this->vacancy);

        $validated = $this->validate((new StoreApplicationRequest)->rules($this->salary_behavior));

        Auth::user()->profiles()->findOrFail($validated['profile_id']);

        $validated['earliest_entry_date'] = filled($validated['earliest_entry_date']) ? $validated['earliest_entry_date'] : null;

        $application = $createApplicationFromVacancy->handle($this->vacancy, $validated);

        $this->redirectRoute('applications.show', $application, navigate: true);
    }
}; ?>

<div>
    @if ($vacancy->application)
        <flux:button icon="document-text" :size="$size" :href="route('applications.show', $vacancy->application)" wire:navigate>
            {{ __('Application') }}
        </flux:button>
    @else
        <flux:button icon="pencil-square" variant="primary" :size="$size" wire:click="open">
            {{ __('Write application') }}
        </flux:button>

        <x-flyout :name="'create-application-modal-'.$vacancy->id">
            <flux:heading size="xl" level="1">{{ __('Write application') }}</flux:heading>
            <flux:subheading>{{ $vacancy->company_name ?: __('No company specified') }} · {{ $vacancy->title ?: __('Untitled Vacancy') }}</flux:subheading>

            <flux:separator variant="subtle" />

            <form class="space-y-6" wire:submit="save">
                <flux:error name="vacancy" />

                <flux:select wire:model.live="profile_id" :label="__('Profile')" required>
                    <flux:select.option value="" selected hidden>{{ __('Choose profile...') }}</flux:select.option>
                    @foreach (Auth::user()->profiles()->pluck('name', 'id') as $id => $name)
                    <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="form_of_address" :label="__('Form of Address')">
                    @foreach (FormOfAddress::cases() as $form)
                    <flux:select.option :value="$form->value">{{ __($form->name) }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:field>
                    <flux:label>{{ __('Salary Expectation') }}</flux:label>

                    <flux:input.group>
                        <flux:select wire:model.live="salary_behavior">
                            @foreach (SalaryBehaviors::cases() as $behavior)
                            <flux:select.option :value="$behavior->value">{{ __($behavior->name) }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:input
                            type="number"
                            wire:model.lazy="salary_desire"
                            :disabled="$salary_behavior !== SalaryBehaviors::Override"
                            :required="$salary_behavior === SalaryBehaviors::Override"
                        />

                        <flux:input.group.suffix>€</flux:input.group.suffix>
                    </flux:input.group>

                    <flux:error name="salary_desire" />
                </flux:field>

                <flux:input type="date" wire:model="earliest_entry_date" :label="__('Earliest Entry Date')" />

                <flux:textarea wire:model="text" :label="__('Text')" rows="16" />

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">{{ __('Create') }}</flux:button>
                    <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
                </div>
            </form>
        </x-flyout>
    @endif
</div>
