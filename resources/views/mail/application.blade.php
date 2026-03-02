@php $form = $application->form_of_address?->value ?? 'formal'; @endphp

<x-mail::message>

{{-- Header --}}
<x-slot:header>
<table cellpadding="0" cellspacing="0" role="presentation">
<tr>
@if ($application->profile?->image_url)
<td><img src="{{ $application->profile?->image_url }}" alt="{{ $application->profile?->name }}" width="40" style="border-radius:20px;border: 3px solid #ddd;vertical-align:top"></td>
<td>&nbsp;&nbsp;&nbsp;</td>
@endif
<th>{{ $application->profile->name }}</th>
</tr>
</table>
</x-slot:header>

{{-- Content --}}
@if ($application->contact_name)
{{ __("mail.application.{$form}.salutation_named", ['name' => $application->contact_name]) }}<br><br>
@else
{{ __("mail.application.{$form}.salutation_generic") }}<br><br>
@endif

{{ __("mail.application.{$form}.body", ['application_title' => $application->title]) }}

<x-mail::button :url="config('app.frontend_url') . '/' . $application->public_id" align="left">
{{ __('mail.application.button') }}
</x-mail::button>

{{ __("mail.application.{$form}.hearing_from_you") }}<br>

{{ __("mail.application.{$form}.closing") }}<br>
{{ $application->profile?->name ?? '' }}

{{-- Footer --}}
<x-slot:footer>
@if ($application->profile?->email)
<a href="mailto:{{ $application->profile->email }}" target="_blank" rel="noopener">{{ $application->profile->email }}</a>
@endif
@if ($application->profile->email && $application->profile?->phone)
&nbsp;&nbsp;&bull;&nbsp;&nbsp;
@endif
@if ($application->profile?->phone)
<a href="tel:{{ $application->profile->phone }}" target="_blank" rel="noopener">{{ $application->profile->phone }}</a>
@endif
</x-slot:footer>
</x-mail::message>
