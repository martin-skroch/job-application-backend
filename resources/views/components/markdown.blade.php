@use('League\CommonMark\CommonMarkConverter')
@use('League\CommonMark\Util\HtmlFilter')

@php
    $converter = new CommonMarkConverter([
        'html_input' => HtmlFilter::ALLOW,
        'allow_unsafe_links' => false,
    ])
@endphp

<div {{ $attributes->class('prose prose-neutral dark:prose-invert max-w-none') }}>
    @if ($slot->isNotEmpty())
        {!! $converter->convert($slot ?? 'Test')  !!}
    @else
        <div class="text-zinc-500">{!! __('-') !!}</div>
    @endif
</div>
