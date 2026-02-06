@props(['selected' => null])

<div x-data="tabs" {{ $attributes->merge(['class' => 'w-full']) }}>
    {{ $slot }}
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tabs', () => ({
            selected: '{{ $selected }}',
            select(name) {
                this.selected = name;
            }
        }))
    })
</script>
