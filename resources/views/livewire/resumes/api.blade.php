<?php

use App\Http\Requests\UpdateResumeRequest;
use App\Models\User;
use App\Models\Resume;
use Flux\Flux;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Resume $resume;

    public function mount(): void
    {
        $this->authorize('view', $this->resume);
    }
}; ?>
<section class="space-y-6">
    <x-resumes.layout :resume="$resume" :heading="__('Overview')" :subheading="__('Manage your personal data.')">
        <table class="border border-gray-300">
            <tbody>
                <tr>
                    <th class="border border-gray-300 p-2 text-left">{{ __('URL') }}</th>
                    <td class="border border-gray-300 p-2 text-left"><code>{{ route('api.resume', $resume) }}</code></td>
                </tr>
                <tr>
                    <th class="border border-gray-300 p-2 text-left">{{ __('Token') }}</th>
                    <td class="border border-gray-300 p-2 text-left"><code>{{ $resume->token }}</code></td>
                </tr>
            </tbody>
        </table>
    </x-resumes.layout>
</section>
