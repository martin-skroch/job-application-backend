<?php

use App\Enum\FormOfAddress;
use App\Enum\SalaryBehaviors;
use App\Models\Application;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public Application $application;

    public ?string $profile_id = null;
    public ?FormOfAddress $form_of_address = null;
    public ?SalaryBehaviors $salary_behavior = null;
    public ?int $salary_desire = null;
    public ?string $text = null;

    public function mount(): void
    {
        $this->authorize('view', $this->application);
        $this->fillFromApplication();
    }

    private function fillFromApplication(): void
    {
        $this->profile_id = $this->application->profile?->id;
        $this->form_of_address = $this->application->form_of_address;
        $this->salary_behavior = $this->application->salary_behavior;
        $this->salary_desire = $this->application->salary_desire;
        $this->text = $this->application->text;
    }

    public function open(): void
    {
        $this->authorize('update', $this->application);
        $this->fillFromApplication();

        Flux::modal('edit-application-modal')->show();
    }

    public function save(): void
    {
        $this->authorize('update', $this->application);

        $validated = $this->validate([
            'profile_id' => ['required', 'string', 'ulid'],
            'form_of_address' => ['required', Rule::enum(FormOfAddress::class)],
            'salary_behavior' => ['required', Rule::enum(SalaryBehaviors::class)],
            'salary_desire' => ['integer', $this->salary_behavior === SalaryBehaviors::Override ? 'required' : 'nullable'],
            'text' => ['nullable', 'string'],
        ]);

        Auth::user()->applications()->where('id', $this->application->id)->update($validated);

        $this->application->refresh();

        Flux::modal('edit-application-modal')->close();

        $this->dispatch('application-saved');
    }
}; ?>

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Application') }}</flux:heading>
        <flux:button icon="pencil-square" variant="ghost" size="sm" wire:click="open">
            {{ __('Edit') }}
        </flux:button>
    </div>

    <flux:callout class="xl:p-6">
        <div class="flex max-xl:flex-col gap-6 xl:gap-12">
            {{-- Cover Letter --}}
            <div class="xl:w-3/4 space-y-6">
                <flux:heading>{{ __('Cover Letter') }}</flux:heading>
                <div>
                    @if ($application->text)
                        <x-markdown>{{ $application->text }}</x-markdown>
                    @else
                        <span class="text-zinc-400">—</span>
                    @endif
                </div>
            </div>

            <div role="none" class="border-0 bg-zinc-800/5 dark:bg-white/10 max-xl:h-px xl:w-px [print-color-adjust:exact]"></div>

            {{-- Profile & Salary --}}
            <div class="xl:w-1/4 space-y-6">
                <flux:heading>{{ __('Profile') }}</flux:heading>
                <div class="space-y-4">
                    @if ($application->profile)
                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</p>
                            <p>{{ $application->profile->name }}</p>
                        </div>

                        @if ($application->profile->title)
                            <div class="space-y-1">
                                <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Title') }}</p>
                                <p>{{ $application->profile->title }}</p>
                            </div>
                        @endif

                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</p>
                            @if ($application->profile->email)
                                <a href="mailto:{{ $application->profile->email }}" class="text-accent hover:underline">
                                    {{ $application->profile->email }}
                                </a>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Salary') }}</p>
                            @if ($application->salary_behavior === SalaryBehaviors::Hidden)
                                <span class="text-zinc-400">{{ __('Hidden') }}</span>
                            @elseif ($application->salary_behavior === SalaryBehaviors::Override && $application->salary_desire)
                                <p>{{ number_format($application->salary_desire, 0, ',', '.') }} €</p>
                            @elseif ($application->salary_behavior === SalaryBehaviors::Inherit && $application->profile->salary_desire)
                                <p>{{ number_format($application->profile->salary_desire, 0, ',', '.') }} € <span class="text-zinc-400">({{ __('from profile') }})</span></p>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </div>

                        <div class="space-y-1">
                            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Form of Address') }}</p>
                            <p>{{ __($application->form_of_address->name) }}</p>
                        </div>
                    @else
                        <p class="text-zinc-400">{{ __('No profile selected.') }}</p>
                    @endif
                </div>

                <livewire:applications.publish as="switch" :application="$application" />
            </div>
        </div>
    </flux:callout>

    <x-flyout name="edit-application-modal">
        <flux:heading size="xl" level="1">{{ __('Edit Application') }}</flux:heading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="save">
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

            <flux:textarea wire:model="text" :label="__('Cover Letter')" rows="16" resize="vertical" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>
</div>
