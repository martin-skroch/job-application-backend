@props(['header' => null, 'footer' => null])
<x-mail::layout>
    {{-- Header --}}
    <x-slot:header>
        <x-mail::header :url="blank($header) ? config('app.url') : null">
            {{ blank($header) ? config('app.name') : $header }}
        </x-mail::header>
    </x-slot:header>

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot:subcopy>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            @empty ($footer)
            © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
            @else
            {!! $footer !!}
            @endif
        </x-mail::footer>
    </x-slot:footer>
</x-mail::layout>
