<?php

use App\Models\Resume;
use Livewire\Volt\Component;

new class extends Component {
    public Resume $resume;

    public bool $apiActive = true;
    public ?string $apiToken = null;

    public function mount(): void
    {
        $this->authorize('view', $this->resume);

        $this->apiActive = $this->resume->api_active;
        $this->apiToken = $this->resume->api_token;
    }

    public function save()
    {
        $this->resume->update([
            'api_active' => $this->apiActive
        ]);
    }
}; ?>
<section class="space-y-6">
    <x-resumes.layout :resume="$resume" :heading="__('Overview')" :subheading="__('Manage your personal data.')">

        <div class="space-y-6">
            <flux:switch wire:change="save" wire:model.live="apiActive" :label="__('Enable API Endpoint')" align="left" />

            @if ($apiActive)
            <flux:callout>
                <div class="p-4 space-y-6">
                    <flux:input :label="__('Token')" :value="$apiToken" readonly copyable />
                    <flux:input :label="__('Resume')" :value="route('api.resume', $resume)" readonly copyable />
                    <flux:input :label="__('Experiences')" :value="route('api.resume.experiences', $resume)" readonly copyable />
                    <flux:input :label="__('Skills')" :value="route('api.resume.skills', $resume)" readonly copyable />
                </div>
            </flux:callout>
            @endif
        </div>
    </x-resumes.layout>
</section>
