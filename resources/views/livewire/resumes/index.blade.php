<?php

use App\Models\Resume;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new class extends Component {
    use WithPagination;

    public function with(): array
    {
        $resumes = Resume::paginate();

        return compact('resumes');
    }

    public function delete(string $id): void
    {
        $resume = Resume::findOrFail($id);

        $resume->delete();

        $this->redirect(route('resumes.index'), navigate: true);
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center">
        <div class="grow">
            <flux:heading size="xl" level="1">{{ __('Resumes') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Manage you resumes') }}</flux:subheading>
        </div>
        <div>
            <flux:button :href="route('resumes.create')" variant="primary" wire:navigate>
                {{ __('Create') }}
            </flux:button>
        </div>
    </div>

    <flux:separator variant="subtle" />

    <div class="grid lg:grid-cols-2 2xl:grid-cols-3 gap-6">
        @foreach ($resumes as $resume)
        <x-card :href="route('resumes.show', $resume)" wire:key="{{ $resume->id }}" wire:navigate>
            <x-slot:heading>{{ $resume->name }}</x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div class="flex items-center gap-2">
                    @php $experienceCount = $resume->experiences()->count(); @endphp
                    <flux:badge size="sm">{{ $experienceCount }}</flux:badge>
                    {{ trans_choice('Experience|Experiences', $experienceCount) }}
                </div>

                <div class="flex items-center gap-2">
                    @php $skillCount = $resume->skills()->count(); @endphp
                    <flux:badge size="sm">{{ $skillCount }}</flux:badge>
                    {{ trans_choice('Skill|Skills', $skillCount) }}
                </div>
            </div>
        </x-card>
        @endforeach
    </div>

    {{ $resumes->links() }}
</section>
