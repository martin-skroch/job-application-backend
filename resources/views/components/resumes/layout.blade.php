<div class="relative mb-6 w-full">
    <flux:heading size="xl" level="1">{{ __('Resume') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ __('Manage your experiences, skills, certificates and languages.') }}</flux:subheading>
    <flux:separator variant="subtle" />
</div>

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('resumes.show', $resume)" :current="request()->routeIs('resumes.show')" wire:navigate>
                {{ __('Person') }}
            </flux:navlist.item>

            <flux:navlist.item :href="route('resumes.experiences', $resume)" :current="request()->routeIs('resumes.experiences')" wire:navigate>
                {{ __('Experiences') }}
            </flux:navlist.item>

            <flux:navlist.item :href="route('resumes.skills', $resume)" :current="request()->routeIs('resumes.skills')" wire:navigate>
                {{ __('Skills') }}
            </flux:navlist.item>

            <flux:navlist.item :href="route('resumes.settings', $resume)" :current="request()->routeIs('resumes.settings')" wire:navigate>
                {{ __('Settings') }}
            </flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="flex items-center">
            <div class="grow">
                <flux:heading size="lg" level="2">{{ $heading ?? '' }}</flux:heading>
                @isset($subheading)<flux:subheading>{{ $subheading }}</flux:subheading>@endisset
            </div>
            @isset($actions)
            <div class="flex items-center gap-6">
                {{ $actions }}
            </div>
            @endisset
        </div>

        <flux:separator class="mt-6" variant="subtle" />

        <div class="mt-6 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
