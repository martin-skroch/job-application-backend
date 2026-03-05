<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl" level="1">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Overview of your applications') }}</flux:subheading>
        </div>

        <livewire:dashboard.application-status-overview />
    </div>
</x-layouts::app>
