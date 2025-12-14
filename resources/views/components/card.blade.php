@php
    if ($attributes->has('href')) {
        $attributes = $attributes->class('hover:bg-zinc-50 dark:hover:bg-zinc-700');
    }
@endphp

<flux:callout {{ $attributes->class('relative p-4')->only('class') }}>

    @isset ($heading)
    <h3 {{ $heading->attributes->class('text-lg font-medium border-b border-dotted border-zinc-500 pb-3 mb-3') }}>
        {{ $heading }}
    </h3>
    @endisset

    @isset ($slot)
    <div {{ $slot->attributes->class('text-sm') }}>
        {{ $slot }}
    </div>
    @endisset

    @if ($attributes->has('href'))
    <a {{ $attributes->except('class')->class('absolute inset-0 z-0') }}></a>
    @endif
</flux:callout>
