@props(['name'])

<div x-show="selected === '{{ $name }}'" x-cloak {{ $attributes->merge(['class' => 'py-4']) }}>
    {{ $slot }}
</div>
