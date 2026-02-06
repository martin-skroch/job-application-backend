@props(['name', 'icon'])

<flux:navbar.item x-on:click="select('{{ $name }}')" x-bind:data-current="selected === '{{ $name }}'" {{ $attributes->merge(['icon' => $icon ?? null]) }}>
    {{ $slot }}
</flux:navbar.item>
