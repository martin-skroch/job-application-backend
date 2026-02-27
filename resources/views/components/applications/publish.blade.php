<?php

use App\Actions\PublishApplication;
use App\Actions\UnpublishApplication;
use App\Models\Application;
use Livewire\Component;

new class extends Component {
    public string $as = 'button';
    public string $size;
    public bool $public = false;
    public Application $application;
    private PublishApplication $publishApplication;
    private UnpublishApplication $unpublishApplication;


    public function boot(
        PublishApplication $publishApplication,
        UnpublishApplication $unpublishApplication
    ) {
        $this->publishApplication = $publishApplication;
        $this->unpublishApplication = $unpublishApplication;
    }

    public function mount(Application $application): void
    {
        $this->application = $application;
        $this->public = $application->isPublic();
    }

    public function updatedPublic(): void
    {
        if ($this->public) {
            $this->publish();
        } else {
            $this->unpublish();
        }
    }

    public function publish(): void
    {
        $this->publishApplication->handle($this->application);
        $this->dispatch('publication-updated');
    }

    public function unpublish(): void
    {
        $this->unpublishApplication->handle($this->application);
        $this->dispatch('publication-updated');
    }
};
?>

<div>
    @if ($as === 'menu.item')

        @if ($application->isPublic())
        <flux:menu.item icon="eye-slash" wire:click="unpublish">
            {{ __('Unpublish') }}
        </flux:menu.item>
        @else
        <flux:menu.item icon="eye" wire:click="publish">
            {{ __('Publish') }}
        </flux:menu.item>
        @endif

    @elseif($as === 'switch')

        <flux:input.group>
            <flux:input.group.prefix>
                <flux:switch wire:model.live="public" />
            </flux:input.group.prefix>

            @if ($application->isPublic())
                <flux:button :size="$size" icon="arrow-top-right-on-square" :href="config('app.frontend_url') . '/' . $application->public_id" target="_blank" rel="noopener">
                    {{ $application->public_id }}
                </flux:button>
            @else
                <flux:button :size="$size" disabled>
                    {{ __('Unpublished') }}
                </flux:button>
            @endif
        </flux:input.group>

    @else

        <flux:input.group>
            @if ($application->isPublic())
                <flux:button :size="$size" icon="arrow-top-right-on-square" :href="config('app.frontend_url') . '/' . $application->public_id" target="_blank" rel="noopener">
                    {{ $application->public_id }}
                </flux:button>

                <flux:button :size="$size" icon="eye" wire:click="unpublish" :tooltip="__('Unpublish')" />
            @else
                <flux:button :size="$size" disabled>
                    {{ __('Unpublished') }}
                </flux:button>

                <flux:button :size="$size" icon="eye-slash" wire:click="publish" :tooltip="__('Publish')" />
            @endif
        </flux:input.group>

    @endif
</div>
