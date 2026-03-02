@props(['url'])
<tr>
<td align="center" style="padding-top:16px;padding-bottom:16px">
@if (filled($url))
<a href="{{ $url }}" style="display: inline-block;">
@endif
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
{!! $slot !!}
@endif
@if (filled($url))
</a>
@endif
</td>
</tr>
