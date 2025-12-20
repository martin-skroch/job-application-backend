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

        <div class="space-y-4 -mt-4">
            <flux:switch wire:change="save" wire:model.live="apiActive" :label="__('Enable API Endpoint')" align="left" />

            @if ($apiActive)
                <flux:input :value="route('api.resume', $resume)" readonly copyable />
                <flux:input :value="$apiToken" readonly copyable />

                <h3>{{ __('Example') }}</h3>
                <pre class="bg-zinc-700 border border-zinc-500 rounded-md p-6">curl {{ route('api.resume', $resume) }} \
    -H "Accept: application/json" \
    -H "Authorization: Bearer {{ $apiToken }}"</pre>
            @endif
        </div>
    </x-resumes.layout>
</section>
