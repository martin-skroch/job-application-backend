<?php

use App\Models\File;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?string $experienceId = null;
    public ?Collection $files = null;

    public ?string $editingFileId = null;
    public bool $isEditingFile = false;

    public ?string $fileTitle = null;
    public $fileObject = null;

    #[On('manage-experience-files')]
    public function open(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $this->experienceId = $id;
        $this->files = File::where('experience_id', $id)->get();

        Flux::modal('experience-files-modal')->show();
    }

    #[On('attach-experience-file')]
    public function openAddFile(string $id): void
    {
        if (!Str::isUlid($id)) {
            return;
        }

        $this->resetFileForm();
        $this->experienceId = $id;

        Flux::modal('experience-file-modal')->show();
    }

    public function addFile(): void
    {
        $this->resetFileForm();

        Flux::modal('experience-file-modal')->show();
    }

    public function editFile(string $fileId): void
    {
        if (!Str::isUlid($fileId)) {
            return;
        }

        $file = File::where('id', $fileId)
            ->where('experience_id', $this->experienceId)
            ->firstOrFail();

        $this->resetFileForm();
        $this->editingFileId = $fileId;
        $this->isEditingFile = true;
        $this->fileTitle = $file->title;

        Flux::modal('experience-file-modal')->show();
    }

    public function saveFile(): void
    {
        $this->validate([
            'fileTitle' => 'nullable|string|max:255',
            'fileObject' => $this->isEditingFile
                ? 'nullable|file|mimetypes:application/pdf|max:5120'
                : 'required|file|mimetypes:application/pdf|max:5120',
        ]);

        if ($this->isEditingFile) {
            $file = File::where('id', $this->editingFileId)
                ->where('experience_id', $this->experienceId)
                ->firstOrFail();

            $updates = ['title' => $this->fileTitle ?? $file->title];

            if ($this->fileObject instanceof TemporaryUploadedFile) {
                Storage::delete($file->path);

                $mime = $this->fileObject->getMimeType();
                $size = $this->fileObject->getSize();
                $path = $this->fileObject->store(path: 'files');

                $updates['path'] = $path;
                $updates['mime'] = $mime;
                $updates['size'] = $size;
            }

            $file->update($updates);
        } else {
            if (!$this->fileObject instanceof TemporaryUploadedFile) {
                return;
            }

            $title = $this->fileTitle ?? $this->fileObject->getClientOriginalName();
            $mime = $this->fileObject->getMimeType();
            $size = $this->fileObject->getSize();
            $path = $this->fileObject->store(path: 'files');

            File::forceCreate([
                'title' => $title,
                'path' => $path,
                'mime' => $mime,
                'size' => $size,
                'experience_id' => $this->experienceId,
            ]);
        }

        Flux::modal('experience-file-modal')->close();

        $this->resetFileForm();

        $this->files = File::where('experience_id', $this->experienceId)->get();
    }

    public function deleteFile(string $fileId): void
    {
        if (!Str::isUlid($fileId)) {
            return;
        }

        $file = File::where('id', $fileId)
            ->where('experience_id', $this->experienceId)
            ->first();

        if ($file) {
            Storage::delete($file->path);
            $file->delete();
        }

        $this->files = File::where('experience_id', $this->experienceId)->get();
    }

    private function resetFileForm(): void
    {
        $this->reset(['fileTitle', 'fileObject', 'editingFileId', 'isEditingFile']);
        $this->resetErrorBag();
    }
};
?>

<div>
    <x-flyout name="experience-files-modal">
        <div class="flex items-center pr-8">
            <div class="grow">
                <flux:heading size="xl" level="1">{{ __('Manage files') }}</flux:heading>
            </div>

            <flux:button wire:click="addFile">{{ __('Add file') }}</flux:button>
        </div>

        <flux:separator variant="subtle" />

        @if ($files)
        <div class="space-y-3">
            @foreach ($files as $file)
            <flux:callout wire:key="file-{{ $file->id }}" inline>
                <flux:callout.heading>{{ $file->title }}</flux:callout.heading>

                <x-slot name="actions">
                    <flux:dropdown>
                        <flux:button icon="ellipsis-horizontal" variant="ghost" />

                        <flux:menu>
                            <flux:menu.item icon="arrow-top-right-on-square" :href="route('file', $file)" target="_blank">
                                {{ __('View') }}
                            </flux:menu.item>

                            <flux:menu.item icon="pencil-square" wire:click="editFile('{{ $file->id }}')">
                                {{ __('Edit') }}
                            </flux:menu.item>

                            <flux:menu.separator />

                            <flux:menu.item variant="danger" icon="trash" wire:click="deleteFile('{{ $file->id }}')" wire:confirm="{{ __('Are you sure you want to delete this file?') }}">
                                {{ __('Delete') }}
                            </flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </x-slot>
            </flux:callout>
            @endforeach
        </div>
        @endif
    </x-flyout>

    <x-flyout name="experience-file-modal">
        <flux:heading size="xl" level="1">{{ $isEditingFile ? __('Edit file') : __('Add file') }}</flux:heading>
        <flux:separator variant="subtle" />

        <form class="space-y-6" wire:submit="saveFile">
            <flux:input type="text" wire:model="fileTitle" :label="__('Title')" />

            <flux:field>
                <flux:label>{{ $isEditingFile ? __('New file (optional)') : __('File') }}</flux:label>

                <flux:input.group class="relative">
                    <flux:avatar icon="paper-clip" class="rounded-e-none" />
                    <input wire:model="fileObject" class="absolute inset-0 opacity-0 z-10" type="file">
                    <flux:input :placeholder="$fileObject?->getClientOriginalName() ?? __('Upload a file')" />
                </flux:input.group>

                <flux:error name="fileObject" />
            </flux:field>

            <div class="inline-flex items-center gap-4">
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" type="button" x-on:click="$flux.modals().close()">{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </x-flyout>
</div>
