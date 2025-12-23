<?php

use Flux\Flux;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public ?string $expiredAt = null;
    public ?string $token = null;
    public ?string $tokenToDelete = null;

    public function open(): void
    {
        Flux::modal('token-modal')->show();
    }

    public function generate(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'expiredAt' => ['nullable', 'date', 'after:today'],
        ]);

        $expiredAt = $this->expiredAt;

        if ($expiredAt !== null) {
            $expiredAt = new DateTimeImmutable($expiredAt . ' ' . now()->format('H:m:s'));
        }

        $this->token = Auth::user()->createToken($this->name, expiresAt: $expiredAt)->plainTextToken;

        Flux::modal('token-modal')->close();

        $this->resetForm();
    }

    public function confirm(string $id): void
    {
        $this->tokenToDelete = $id;

        Flux::modal('confirm-modal')->show();
    }

    public function delete(): void
    {
        Auth::user()->tokens()->where('id', $this->tokenToDelete)->delete();

        $this->reset('token', 'tokenToDelete');

        Flux::modal('confirm-modal')->close();
    }

    public function resetForm(): void
    {
        $this->reset('name', 'expiredAt');
        $this->resetErrorBag();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Personal access tokens')">
        <x-slot:actions>
            <flux:button wire:click="open">{{ __('Generate new token') }}</flux:button>
        </x-slot>

        <div class="space-y-4">
            @if ($token !== null)
            <flux:callout color="emerald" inline>
                <flux:callout.text>{{ __('Make sure to copy your personal access token now. You won’t be able to see it again!') }}</flux:callout.text>
                <flux:input :value="$token" readonly copyable />
            </flux:callout>
            @endif

            @foreach (Auth::user()->tokens as $token)
            <flux:callout inline>
                <flux:callout.heading>{{ $token->name }}</flux:callout.heading>

                <flux:callout.text>
                    @if ($token->expires_at instanceof DateTimeInterface)
                        @if($token->expires_at->withTimezone()->isAfter(now()))
                            {{ __('Expires on :date', ['date' => $token->expires_at->withTimezone()->isoFormat('LLLL')]) }}
                        @else
                            {{ __('Expired since :date', ['date' => $token->expires_at->withTimezone()->isoFormat('LLLL')]) }}
                        @endif
                    @else
                        {{ __('No expiration date.') }}
                    @endif
                </flux:callout.text>

                <flux:callout.text>
                    @if ($token->last_used_at instanceof DateTimeInterface)
                        {{ __('Last used on :date', ['date' => $token->last_used_at->withTimezone()->isoFormat('LLLL')]) }}
                    @else
                        {{ __('Never used.') }}
                    @endif
                </flux:callout.text>

                <x-slot name="actions">
                    <flux:button variant="danger" size="sm" wire:click="confirm('{{ $token->id }}')">{{ __('Delete') }}</flux:button>
                </x-slot>
            </flux:callout>
            @endforeach
        </div>
    </x-settings.layout>

    <x-flyout name="token-modal">
        <flux:heading size="xl">{{ __('New personal access token') }}</flux:heading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="generate">
            <flux:input wire:model="name" :label="__('Name')" type="text" />
            <flux:input wire:model="expiredAt" type="date" min="{{ now()->format('Y-m-d H:m:s') }}" label="Date" />

            <div class="inline-flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Create') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>

    <flux:modal name="confirm-modal" wire:close="$set('tokenToDelete', null)" :dismissible="false">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Are you sure you want to delete this token?') }}</flux:heading>

            <flux:text>{{ __('Any applications or scripts using this token will no longer be able to access the API. You cannot undo this action.') }}</flux:text>

            <form class="flex justify-end gap-2" wire:submit="delete">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
            </form>
        </div>
    </flux:modal>
</section>
