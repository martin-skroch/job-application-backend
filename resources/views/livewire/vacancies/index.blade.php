<?php

use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Models\Vacancy;
use App\Enum\SalaryPeriod;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;
    public Vacancy $vacancy;

    public bool $isEditing = false;
    public ?string $vacancyId = null;

    public string $title = '';
    public ?string $source = '';
    public ?string $content = '';
    public ?string $salary_min = '';
    public ?string $salary_max = '';
    public ?string $salary_period = null;
    public ?Collection $workplace;
    public ?string $weekhours = '';
    public ?string $location = '';
    public ?string $company = '';
    public ?string $address = '';
    public ?string $contact = '';
    public ?string $email = '';
    public ?string $website = '';

    public function with(): array
    {
        $vacancies = Vacancy::paginate();

        return compact('vacancies');
    }

    public function open(?string $id = null): void
    {
        $this->resetForm();

        if (Str::isUlid($id)) {
            $this->isEditing = true;
            $this->vacancyId = $id;

            $vacancy = Vacancy::findOrFail($this->vacancyId);

            $this->title = $vacancy->title;
            $this->source = $vacancy->source;
            $this->content = $vacancy->content;
            $this->salary_min = $vacancy->salary_min;
            $this->salary_max = $vacancy->salary_max;
            $this->salary_period = $vacancy->salary_period?->value;
            $this->workplace = $vacancy->workplace;
            $this->weekhours = $vacancy->weekhours;
            $this->location = $vacancy->location;
            $this->company = $vacancy->company;
            $this->address = $vacancy->address;
            $this->contact = $vacancy->contact;
            $this->email = $vacancy->email;
            $this->website = $vacancy->website;
        }

        Flux::modal('vacancy-modal')->show();
    }

    public function save(?string $id = null): void
    {
        $hasId = $this->vacancyId !== null && Str::isUlid($this->vacancyId);
        $request = $hasId ? new UpdateVacancyRequest() : new StoreVacancyRequest();
        $validated = $this->validate($request->rules());

        if ($hasId) {
            Vacancy::where('id', $this->vacancyId)->update($validated);
        } else {
            Vacancy::create($validated);
        }

        Flux::modal('vacancy-modal')->close();

        $this->resetForm();
    }

    public function delete(string $id): void
    {
        Vacancy::where('id', $this->vacancyId)->delete();

        Flux::modal('vacancy-modal')->close();

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'title',
            'source',
            'content',
            'salary_min',
            'salary_max',
            'salary_period',
            'workplace',
            'weekhours',
            'location',
            'company',
            'address',
            'contact',
            'email',
            'website',
        ]);

        $this->resetErrorBag();

        $this->isEditing = false;
        $this->vacancyId = null;
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ __('Vacancies') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage you vacancies') }}</flux:subheading>
        </div>
        <div>
            <flux:button variant="primary" :loading="false" wire:click="open">
                {{ __('Create Vacancy') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <div class="grid lg:grid-cols-2 2xl:grid-cols-3 gap-6">
        @foreach ($vacancies as $vacancy)
            <x-card :href="route('vacancies.show', $vacancy)" wire:key="{{ $vacancy->id }}" wire:navigate>
                <x-slot:heading class="grid grid-cols-3 gap-6">
                    <div class="col-span-2 truncate">{{ $vacancy->title }}</div>
                    <div class="col-span-1 flex items-center justify-end gap-1 relative z-10">
                        @if ($vacancy->website)
                            <flux:button :href="$vacancy->website" variant="subtle" target="_blank" rel="noopener" size="sm"
                                icon="globe-europe-africa" />
                        @endif
                        <flux:button wire:click="open('{{ $vacancy->id }}')" variant="subtle" size="sm" icon="pencil-square"
                            wire:navigate />
                    </div>
                    </x-slot>

                    <x-slot:text class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <flux:icon name="building-office" variant="micro" />
                                <div class="truncate">{{ $vacancy->company }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:icon name="computer-desktop" variant="micro" />
                                <div>{{ $vacancy->workplaceFormatted }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:icon name="map" variant="micro" />
                                <div class="truncate">{{ $vacancy->location }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <flux:icon name="clock" variant="micro" />
                                <div>{{ $vacancy->weekhours }} {{ __('Hours per Week') }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <flux:icon name="banknotes" variant="micro" />
                                <div>{{ $vacancy->salary->monthly }} € <span
                                        class="text-zinc-400 ml-1">({{ __('monthly') }})</span></div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:icon name="banknotes" variant="micro" />
                                <div>{{ $vacancy->salary->yearly }} € <span
                                        class="text-zinc-400 ml-1">({{ __('yearly') }})</span></div>
                            </div>
                        </div>
                        </x-slot>
            </x-card>
        @endforeach
    </div>

    {{ $vacancies->links() }}

    <x-flyout name="vacancy-modal" wire:close="resetForm">
        <flux:heading size="xl" level="1">{{ $isEditing ? __('Edit') : __('Create') }}</flux:heading>
        <flux:subheading size="lg">{{ __('Manage your experiences') }}</flux:subheading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="save">
            <flux:input wire:model="title" :label="__('Title')" required />
            <flux:input wire:model="source" :label="__('Source')" />

            <div class="grid grid-cols-6 gap-6">
                <flux:field class="col-span-6">
                    <flux:label>{{ __('Salary period') }}</flux:label>
                    <flux:radio.group class="col-span-6" wire:model="salary_period" :placeholder="__('Select')"
                        variant="segmented">
                        @foreach (SalaryPeriod::names() as $value => $label)
                            <flux:radio :value="$value" :label="$label" />
                        @endforeach
                    </flux:radio.group>
                    <flux:error name="salary_period" />
                </flux:field>

                <flux:field class="max-2xl:col-span-6 col-span-3">
                    <flux:label>{{ __('Minimum salary') }}</flux:label>
                    <flux:input.group>
                        <flux:input wire:model="salary_min" />
                        <flux:input.group.suffix>€</flux:input.group.suffix>
                    </flux:input.group>
                    <flux:error name="salary_min" />
                </flux:field>

                <flux:field class="max-2xl:col-span-6 col-span-3">
                    <flux:label>{{ __('Maximum salary') }}</flux:label>
                    <flux:input.group>
                        <flux:input wire:model="salary_max" />
                        <flux:input.group.suffix>€</flux:input.group.suffix>
                    </flux:input.group>
                    <flux:error name="salary_max" />
                </flux:field>
            </div>

            <div class="grid 2xl:grid-cols-2 gap-6">
                <flux:input wire:model="weekhours" :label="__('Weekhours')" />
                <flux:input wire:model="location" :label="__('Location')" />
            </div>

            <flux:field>
                <flux:label>{{ __('Workspace') }}</flux:label>
                <div class="flex gap-6 border rounded-md border-zinc-600 py-2.5 px-3">
                    <flux:checkbox wire:model="workplace" value="location" :label="__('Location')" />
                    <flux:checkbox wire:model="workplace" value="hybrid" :label="__('Hybrid')" />
                    <flux:checkbox wire:model="workplace" value="remote" :label="__('Remote')" />
                </div>
                <flux:error name="workplace" />
            </flux:field>

            <flux:textarea wire:model="content" :label="__('Content')" />

            <flux:input wire:model="company" :label="__('Company name')" />

            <flux:textarea wire:model="address" :label="__('Company address')" rows="2" />

            <div class="grid 2xl:grid-cols-2 gap-6">
                <flux:input wire:model="contact" :label="__('Company contact')" />
                <flux:input wire:model="email" :label="__('Company email')" />
            </div>

            <flux:input wire:model="website" :label="__('Company website')" />

            <div class="inline-flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ $isEditing ? 'Save' : __('Create') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>

        @if ($vacancyId)
            <flux:separator variant="subtle" />

            <flux:button class="mb-0" variant="danger" wire:click="delete('{{ $vacancyId }}')"
                wire:confirm="{{ __('Are you sure you want to delete this experience?') }}">
                {{ __('Delete') }}
            </flux:button>
        @endif
    </x-flyout>
</section>
