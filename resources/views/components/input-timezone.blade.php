@php
    $attributes = $attributes->merge([
        'label' => __('Timezone'),
        'placeholder' => __('Choose your timezone')
    ]);

    $timezones = [];

    foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $timezone) {
        $parts = explode('/', str_replace('_', ' ', $timezone));

        $continent = array_shift($parts);

        if (is_array($parts)) {
            $timezones[$continent][] = trim($continent . '/' . implode('/', $parts), '/');
        } else {
            $timezones[$continent] = $continent;
        }
    }
@endphp

<flux:select :$attributes>
    @foreach($timezones as $continent => $entries)
        @if(is_array($entries) && count($entries) > 0)
            <optgroup label="{{ $continent }}">
                @foreach ($entries as $entry)
                <flux:select.option :value="$entry">{{ $entry }}</flux:select.option>
                @endforeach
            </optgroup>
        @else
            <flux:select.option :value="$entries">{{ $entries }}</flux:select.option>
        @endif
    @endforeach
</flux:select>
