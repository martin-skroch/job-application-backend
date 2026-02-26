<?php

use App\Actions\SendApplication;
use App\Enum\ApplicationStatus;
use App\Mail\ApplicationMail;
use App\Models\Application;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Application $application;

    public bool $isTest = false;

    private SendApplication $sendApplication;

    public function boot(SendApplication $sendApplication): void
    {
        $this->sendApplication = $sendApplication;
    }

    #[Computed]
    public function issues(): array
    {
        $issues = [];

        if (! $this->application->profile) {
            $issues[] = __('No profile selected.');
        }

        if (blank($this->application->profile?->email)) {
            $issues[] = __('Profile has no email address.');
        }

        if (! $this->isTest) {
            if ($this->application->status() === ApplicationStatus::Sent) {
                $issues[] = __('Application has already been sent.');
            }

            if (blank($this->application->contact_email)) {
                $issues[] = __('Contact email is missing.');
            }
        }

        return $issues;
    }

    #[Computed]
    public function subject(): string
    {
        $subject = __('mail.application.subject', ['title' => $this->application->title]);

        if ($this->isTest) {
            $subject = __('mail.application.subject_test', ['subject' => $subject]);
        }

        return $subject;
    }

    #[Computed]
    public function from(): ?string
    {
        if (blank($this->application->profile?->email)) {
            return null;
        }

        $email = $this->application->profile->email;
        $name = $this->application->profile->name;

        return filled($name) ? "{$name} <{$email}>" : $email;
    }

    #[Computed]
    public function to(): ?string
    {
        if ($this->isTest) {
            return $this->application->profile?->email;
        }

        return $this->application->contact_email;
    }

    #[Computed]
    public function renderedHtml(): string
    {
        return (new ApplicationMail($this->application, isTest: $this->isTest))->renderHtml();
    }

    #[Computed]
    public function renderedText(): string
    {
        return (new ApplicationMail($this->application, isTest: $this->isTest))->renderText();
    }

    #[Computed]
    public function modalName(): string
    {
        return 'send-preview-' . $this->application->id . '-' . ($this->isTest ? 'test' : 'real');
    }

    public function open(): void
    {
        Flux::modal($this->modalName)->show();
    }

    public function send(): void
    {
        $this->authorize('update', $this->application);

        if (! empty($this->issues)) {
            return;
        }

        $this->sendApplication->handle($this->application, setStatus: ! $this->isTest);

        Flux::modal($this->modalName)->close();
    }
}; ?>

<div>
    <flux:menu.item
        icon="{{ $isTest ? 'beaker' : 'paper-airplane' }}"
        :variant="$isTest ? null : 'danger'"
        wire:click="open"
    >
        {{ $isTest ? __('Test Application') : __('Real Application') }}
    </flux:menu.item>

    @teleport('body')
    <x-flyout :name="$this->modalName" class="md:w-3xl space-y-6">
        <flux:heading size="lg">
            {{ $isTest ? __('Preview Test Email') : __('Preview Email') }}
        </flux:heading>

        @if (! empty($this->issues))
            <flux:callout icon="exclamation-triangle" color="yellow">
                <flux:callout.heading>{{ __('Cannot send') }}</flux:callout.heading>
                <flux:callout.text>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($this->issues as $issue)
                            <li>{{ $issue }}</li>
                        @endforeach
                    </ul>
                </flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid md:grid-cols-[6rem_1fr] gap-x-4 gap-y-3 items-baseline">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('From') }}</p>
            <p class="font-mono text-sm break-all">{{ $this->from ?? '—' }}</p>

            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('To') }}</p>
            <p class="font-mono text-sm break-all">{{ $this->to ?? '—' }}</p>

            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Subject') }}</p>
            <p class="text-sm">{{ $this->subject }}</p>
        </div>

        {{-- Email body preview --}}
        <div x-data="{ tab: 'html' }">
            <div class="flex border-b border-zinc-200 dark:border-zinc-700 mb-4">
                <button
                    x-on:click="tab = 'html'"
                    :class="tab === 'html' ? 'border-b-2 border-accent text-accent' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'"
                    class="px-4 py-2 text-sm font-medium -mb-px"
                >HTML</button>
                <button
                    x-on:click="tab = 'text'"
                    :class="tab === 'text' ? 'border-b-2 border-accent text-accent' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'"
                    class="px-4 py-2 text-sm font-medium -mb-px"
                >Text</button>
            </div>

            <div x-show="tab === 'html'" class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
                <iframe srcdoc="{!! e($this->renderedHtml) !!}" class="w-full h-96"></iframe>
            </div>

            <pre x-show="tab === 'text'" class="text-xs font-mono bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg overflow-auto max-h-96 border border-zinc-200 dark:border-zinc-700 whitespace-pre-wrap">{!! e($this->renderedText) !!}</pre>
        </div>

        <div class="flex items-center gap-4">
            <flux:button
                :variant="$isTest ? 'primary' : 'danger'"
                :icon="$isTest ? 'beaker' : 'paper-airplane'"
                wire:click="send"
                :disabled="! empty($this->issues)"
            >
                {{ $isTest ? __('Send Test Email') : __('Send Application') }}
            </flux:button>

            <flux:button
                variant="ghost"
                x-on:click="$flux.modal('{{ $this->modalName }}').close()"
            >
                {{ __('Cancel') }}
            </flux:button>
        </div>
    </x-flyout>
    @endteleport
</div>
