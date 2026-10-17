<?php

use App\Http\Requests\StoreVacancyRequest;
use App\Models\Vacancy;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use League\HTMLToMarkdown\HtmlConverter;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public const array FILTERS = [
        'bookmarked' => 'Bookmarked',
        'applied' => 'Applied',
        'archived' => 'Archived',
    ];

    #[Url]
    public string $filter = 'bookmarked';

    public ?string $title = null;

    public ?string $source = null;

    public ?string $description = null;

    public ?string $company_name = null;

    public bool $descriptionFilled = false;

    public function with(): array
    {
        $query = Auth::user()->vacancies()->with('application');

        match ($this->filter) {
            'applied' => $query->has('application'),
            'archived' => $query->onlyTrashed(),
            default => $query->doesntHave('application'),
        };

        return [
            'filters' => self::FILTERS,
            'vacancies' => $query->paginate(),
        ];
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('create', Vacancy::class);

        $this->resetForm();

        Flux::modal('vacancy-modal')->show();
    }

    public function save(): void
    {
        $this->authorize('create', Vacancy::class);

        $request = new StoreVacancyRequest;
        $validated = $this->validate(
            collect($request->rules())->only(['title', 'source', 'description', 'company_name'])->all(),
            $request->messages(),
        );

        $vacancy = Auth::user()->vacancies()->create($validated);

        Flux::modal('vacancy-modal')->close();

        $this->resetForm();

        $this->redirectRoute('vacancies.show', $vacancy, navigate: true);
    }

    public function updatedDescription(): void
    {
        if (! $this->descriptionFilled) {
            $converter = new HtmlConverter(['strip_tags' => true]);

            $this->description = $converter->convert($this->description ?? '');
            $this->descriptionFilled = true;
        }
    }

    public function archive(string $id): void
    {
        if (! Str::isUlid($id)) {
            return;
        }

        $vacancy = Auth::user()->vacancies()->find($id);

        if (! $vacancy instanceof Vacancy) {
            return;
        }

        $this->authorize('delete', $vacancy);

        $vacancy->delete();
    }

    public function restore(string $id): void
    {
        if (! Str::isUlid($id)) {
            return;
        }

        $vacancy = Auth::user()->vacancies()->onlyTrashed()->find($id);

        if (! $vacancy instanceof Vacancy) {
            return;
        }

        $this->authorize('restore', $vacancy);

        $vacancy->restore();
    }

    public function resetForm(): void
    {
        $this->reset('title', 'source', 'description', 'company_name', 'descriptionFilled');
        $this->resetErrorBag();
    }
}; ?>

<section class="space-y-8">

    {{-- Header --}}
    <div class="flex max-lg:flex-col lg:items-center gap-6">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ __('Vacancies') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Bookmark vacancies and write applications for them') }}</flux:subheading>
        </div>
        <div class="flex max-lg:flex-wrap items-center justify-between lg:justify-end gap-6">
            <flux:button.group>
                @foreach ($filters as $value => $label)
                    <flux:button
                        :href="route('vacancies.index', ['filter' => $value])"
                        :variant="$filter === $value ? 'primary' : null"
                        wire:navigate
                    >
                        {{ __($label) }}
                    </flux:button>
                @endforeach
            </flux:button.group>

            <flux:button icon="plus" variant="primary" wire:click="create">
                {{ __('Bookmark') }}
            </flux:button>
        </div>
    </div>

    <div class="grid 3xl:grid-cols-2 6xl:grid-cols-3 gap-6">

        @forelse ($vacancies as $vacancy)
        <flux:callout class="p-3" inline wire:key="vacancy-{{ $vacancy->id }}">
            <div class="flex max-xl:flex-col xl:items-center gap-6">

                @if (! $vacancy->isArchived())
                    <a href="{{ route('vacancies.show', $vacancy) }}" class="grow hover:bg-white/5 -my-3 py-3 -mx-3 px-3 rounded-lg" wire:navigate>
                @else
                    <div class="grow">
                @endif

                    <flux:heading size="lg" class="mb-1!">{{ $vacancy->company_name ?: __('No company specified') }}</flux:heading>
                    <flux:subheading size="sm">{{ $vacancy->title ?: __('Untitled Vacancy') }}</flux:subheading>

                @if (! $vacancy->isArchived())
                    </a>
                @else
                    </div>
                @endif

                @if ($vacancy->source)
                    <flux:button size="sm" icon="arrow-top-right-on-square" :href="route('redirect', ['url' => $vacancy->source->value()])" target="_blank" rel="noopener">
                        {{ __('Source') }}
                    </flux:button>
                @endif

                @if (! $vacancy->isArchived())
                    <livewire:vacancies.create-application size="sm" :vacancy="$vacancy" :key="'create-application-'.$vacancy->id" />
                @endif

                <x-slot name="controls" class="flex lg:items-center">
                    <flux:dropdown class="ms-auto">
                        <flux:button icon:trailing="ellipsis-horizontal" variant="ghost"></flux:button>

                        <flux:menu>
                            @if (! $vacancy->isArchived())
                            <flux:menu.item icon="document-text" :href="route('vacancies.show', $vacancy)" wire:navigate>
                                {{ __('Details') }}
                            </flux:menu.item>

                            <flux:menu.item icon="archive-box" wire:click="archive('{{ $vacancy->id }}')" wire:confirm="{{ __('Are you sure you want to archive this vacancy?') }}">
                                {{ __('Archive') }}
                            </flux:menu.item>
                            @else
                            <flux:menu.item icon="arrow-uturn-left" wire:click="restore('{{ $vacancy->id }}')" wire:confirm="{{ __('Are you sure you want to restore this vacancy?') }}">
                                {{ __('Restore') }}
                            </flux:menu.item>
                            @endif
                        </flux:menu>
                    </flux:dropdown>
                </x-slot>

            </div>
        </flux:callout>
        @empty
            <flux:text>{{ __('No vacancies found.') }}</flux:text>
        @endforelse

    </div>

    {{ $vacancies->links() }}

    @teleport('body')
    <x-flyout name="vacancy-modal" wire:close="resetForm">
        <flux:heading size="xl" level="1">{{ __('Bookmark vacancy') }}</flux:heading>
        <flux:subheading>{{ __('Only the essentials – you can add the rest later.') }}</flux:subheading>

        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="save">
            <flux:input wire:model="source" :label="__('Source')" type="url" icon="link" autofocus />
            <flux:input wire:model="title" :label="__('Title')" />
            <flux:input wire:model="company_name" :label="__('Company name')" icon="building-office" />
            <flux:textarea wire:model.live.debounce.500ms="description" :label="__('Description')" rows="8" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Bookmark') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>
    @endteleport
</section>
