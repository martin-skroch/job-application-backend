<?php

use App\Actions\PublishApplication;
use App\Actions\UnpublishApplication;
use App\Enum\ApplicationStatus;
use App\Enum\FormOfAddress;
use App\Enum\SalaryBehaviors;
use App\Models\Application;
use App\Models\Profile;
use App\Http\Requests\StoreApplicationRequest;
use App\Http\Requests\UpdateApplicationRequest;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url]
    public ?string $status = 'draft';

    public bool $isEditing = false;
    public ?string $applicationId = null;

    public Application $application;
    public Profile $profile;

    public ?string $title = null;
    public ?string $source = null;
    public ?string $description = null;
    public ?FormOfAddress $form_of_address = FormOfAddress::Formal;
    public ?SalaryBehaviors $salary_behavior = SalaryBehaviors::Hidden;
    public ?int $salary_desire = null;
    public ?string $text = null;
    public ?string $contact_name = null;
    public ?string $contact_email = null;
    public ?string $contact_phone = null;
    public ?string $company_name = null;
    public ?string $company_address = null;
    public ?string $company_website = null;
    public ?string $profile_id = null;
    public ?string $public_id = null;


    private PublishApplication $publishApplication;
    private UnpublishApplication $unpublishApplication;

    public function boot(
        PublishApplication $publishApplication,
        UnpublishApplication $unpublishApplication
    ) {
        $this->publishApplication = $publishApplication;
        $this->unpublishApplication = $unpublishApplication;
    }

    public function with(): array
    {
        $archivedFilter = $this->status === 'archived';
        $statusFilter = $archivedFilter ? null : ApplicationStatus::tryFrom($this->status ?? '');

        $statusItems = collect(ApplicationStatus::cases())
            ->map(fn (ApplicationStatus $case) => [
                'route' => route('applications.index', ['status' => $case->value]),
                'name' => $case->name,
                'value' => $case->value,
                'current' => $statusFilter === $case,
            ]);

        $navigation = $statusItems->merge([[
            'route' => route('applications.index', ['status' => 'archived']),
            'name' => 'Archived',
            'value' => 'archived',
            'current' => $archivedFilter,
        ]]);

        $query = Auth::user()->applications()->with('latestStatusEntry');

        if ($archivedFilter) {
            $query->onlyTrashed();
        } elseif ($statusFilter !== null) {
            $query->whereHas('history', fn ($q) => $q
                ->where('status', $statusFilter->value)
                ->whereNotExists(
                    fn ($sub) => $sub->from('applications_history as newer')
                        ->whereColumn('newer.application_id', 'applications_history.application_id')
                        ->whereNotNull('newer.status')
                        ->where(fn ($w) => $w
                            ->whereColumn('newer.created_at', '>', 'applications_history.created_at')
                            ->orWhere(fn ($w2) => $w2
                                ->whereColumn('newer.created_at', '=', 'applications_history.created_at')
                                ->whereColumn('newer.id', '>', 'applications_history.id')
                            )
                        )
                )
            );
        }

        return [
            'navigation' => $navigation,
            'applications' => $query->paginate(),
        ];
    }

    public function open(?string $id = null): void
    {
        $this->authorize('create', Application::class);

        $this->resetForm();

        if (Str::isUlid($id)) {
            $this->isEditing = true;
            $this->applicationId = $id;

            $application = Application::findOrFail($id);

            $this->application = $application;
            $this->profile = $application->profile;
            $this->form_of_address = $application->form_of_address;
            $this->salary_desire = $application->salary_desire;
            $this->salary_behavior = $application->salary_behavior;

            $this->title = $application->title;
            $this->source = $application->source?->value();
            $this->description = $application->description;
            $this->text = $application->text;

            $this->contact_name = $application->contact_name;
            $this->contact_email = $application->contact_email;
            $this->contact_phone = $application->contact_phone;

            $this->company_name = $application->company_name;
            $this->company_address = $application->company_address;
            $this->company_website = $application->company_website?->value();

            $this->public_id = $application->public_id;
            $this->profile_id = $application->profile?->id;

        }

        Flux::modal('application-modal')->show();
    }

    public function save(): void
    {
        $applications = Auth::user()->applications();
        $request = Str::isUlid($this->applicationId) ? new UpdateApplicationRequest() : new StoreApplicationRequest();
        $validated = $this->validate($request->rules($this->salary_behavior));

        if (Str::isUlid($this->applicationId)) {
            $applications->where('id', $this->applicationId)->update($validated);
        } else {
            $application = $applications->forceCreate($validated);
            $application->history()->create(['status' => ApplicationStatus::Draft]);
        }

        Flux::modal('application-modal')->close();

        $this->resetForm();
    }

    public function publish(?string $id = null): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $application = Auth::user()->applications()->find($id);

        if (!$application instanceof Application) {
            return;
        }

        $this->publishApplication->handle($application);
    }

    public function unpublish(?string $id = null): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $application = Auth::user()->applications()->find($id);

        if (!$application instanceof Application) {
            return;
        }

        $this->unpublishApplication->handle($application);
    }

    public function archive(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $application = Auth::user()->applications()->find($id);

        if (!$application instanceof Application) {
            return;
        }

        $application->delete();
    }

    public function restore(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $application = Auth::user()->applications()->withTrashed()->find($id);

        if (!$application instanceof Application || !$application->isArchived()) {
            return;
        }

        $application->restore();

        $application->history()->create([
            'comment' => __('Application restored from archive.'),
        ]);
    }

    public function resetForm(): void
    {
        parent::reset();
        parent::resetErrorBag();
    }

    public function updatedProfileId()
    {
        $this->profile = Profile::findOrFail($this->profile_id);
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ __('Applications') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage your applications') }}</flux:subheading>
        </div>
        <div>
            <flux:button icon="plus" variant="primary" :loading="false" wire:click="open">
                {{ __('Create') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <div class="flex items-start max-md:flex-col">
        <div class="me-10 w-full pb-4 md:w-[180px] shrink-0">
            <flux:navlist>
                @foreach ($navigation as $item)
                <flux:navlist.item :href="$item['route']" :current="$item['current']" wire:navigate>
                    {{ __($item['name']) }}
                </flux:navlist.item>
                @endforeach
            </flux:navlist>
        </div>

        <flux:separator class="md:hidden" />

        <div class="flex-1 min-w-0 max-md:pt-6 space-y-4">

            @foreach ($applications as $application)
            <flux:callout class="p-3" inline>
                <flux:callout.heading class="text-base!">
                    @if (!$application->isArchived())
                    <a href="{{ route('applications.show', $application) }}" class="hover:text-accent hover:underline" wire:navigate>
                    @endif

                        {{ $application->company_name }}

                    @if (!$application->isArchived())
                    </a>
                    @endif
                </flux:callout.heading>

                <x-slot name="actions" class="flex-wrap me-1! self-center! gap-4">

                    @if ($application->isArchived())
                    <flux:button size="sm" icon="arrow-uturn-left" wire:click="restore('{{ $application->id }}')" wire:confirm="{{ __('Are you sure you want to restore this application?') }}">
                        {{ __('Restore') }}
                    </flux:button>
                    @else
                    <flux:button.group>
                        <flux:button size="sm" :href="route('applications.show', $application)" wire:navigate>
                            {{ __('Show') }}
                        </flux:button>

                        <flux:dropdown>
                            <flux:button size="sm" icon:trailing="chevron-down"></flux:button>
                            <flux:menu>
                                <flux:menu.item wire:click="open('{{ $application->id }}')">
                                    {{ __('Edit') }}
                                </flux:menu.item>

                                <flux:menu.item variant="danger" wire:click="archive('{{ $application->id }}')" wire:confirm="{{ __('Are you sure you want to archive this application?') }}">
                                    {{ __('Delete') }}
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:button.group>
                    @endif

                    <livewire:application-status-modal :application="$application" :key="'status-' . $application->id" />

                    <flux:button.group class="inline-flex">
                        @if ($application->isPublic())
                        <flux:button size="sm" icon="eye" class="font-mono" :href="config('app.frontend_url') . '/' . $application->public_id" target="_blank" rel="noopener">
                            {{ $application->public_id }}
                        </flux:button>
                        @else
                        <flux:button icon="eye-slash" size="sm" disabled>
                            {{ __('Not public') }}
                        </flux:button>
                        @endif

                        @if (!$application->isArchived())
                        <flux:dropdown>
                            <flux:button size="sm" icon:trailing="chevron-down"></flux:button>

                            <flux:menu>
                                @if ($application->isPublic())
                                <flux:menu.item wire:click="unpublish('{{ $application->id }}')">
                                    {{ __('Unpublish') }}
                                </flux:menu.item>
                                @else
                                <flux:menu.item wire:click="publish('{{ $application->id }}')">
                                    {{ __('Publish') }}
                                </flux:menu.item>
                                @endif
                            </flux:menu>
                        </flux:dropdown>
                        @endif
                    </flux:button.group>
                </x-slot>
            </flux:callout>
            @endforeach

            {{ $applications->links() }}

        </div>
    </div>

    <x-flyout name="application-modal" wire:close="resetForm">

        <flux:heading size="xl" level="1">
            {{ __($isEditing ? 'Edit application' : 'Create new application') }}
        </flux:heading>

        <flux:separator variant="subtle" />

        <form class="space-y-16" wire:submit="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="xl" class="mb-2">{{ __('Job offer') }}</flux:heading>
                    <flux:separator variant="subtle" />
                </div>

                    <flux:input wire:model="title" :label="__('Title')" />
                    <flux:input wire:model="source" :label="__('Source')" type="url" icon="link" />
                <flux:textarea wire:model="description" :label="__('Description')" />
            </div>

            <div class="space-y-6">
                <div>
                    <flux:heading size="xl" class="mb-2">{{ __('Application Details') }}</flux:heading>
                    <flux:separator variant="subtle" />
                </div>

                <flux:select wire:model.change="profile_id" :label="__('Profile')" required>
                    <flux:select.option value="" selected hidden>{{ __('Choose profile...') }}</flux:select.option>
                    @foreach (Auth::user()->profiles()->pluck('name',  'id') as $id => $name)
                    <flux:select.option :value="$id">{{ $name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="form_of_address" :label="__('Form of Address')">
                    @foreach (FormOfAddress::cases() as $form)
                    <flux:select.option :value="$form->value">{{ __($form->name) }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:field >
                    <flux:label>{{ __(key: 'Salary Expectation') }}</flux:label>

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

                <flux:textarea wire:model="text" :label="__('Text')" rows="16" />
            </div>

            <div class="space-y-6">
                <div>
                    <flux:heading size="xl" class="mb-2">{{ __('Company') }}</flux:heading>
                    <flux:separator variant="subtle" />
                </div>

                <div class="grid lg:grid-cols-2 gap-6">
                    <flux:input wire:model="company_name" :label="__('Company name')" icon="building-office" />
                    <flux:input wire:model="company_website" :label="__('Company website')" type="url" icon="link" />
                </div>

                <flux:textarea wire:model="company_address" :label="__('Company address')" />
            </div>

            <div class="space-y-6">
                <div>
                    <flux:heading size="xl" class="mb-2">{{ __('Contact') }}</flux:heading>
                    <flux:separator variant="subtle" />
                </div>

                <flux:input wire:model="contact_name" :label="__('Contact name')" icon="user" />

                <div class="grid lg:grid-cols-2 gap-6">
                    <flux:input wire:model="contact_email" :label="__('Contact email')" type="email" icon="at-symbol" />
                    <flux:input wire:model="contact_phone" :label="__('Contact phone')" type="tel" icon="phone" />
                </div>
            </div>

            <div class="flex items-center justify-start gap-4">
                <flux:button variant="primary" type="submit">{{ __($isEditing ? 'Save' : 'Create') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>

    </x-flyout>

</section>
