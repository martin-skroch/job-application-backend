@php $form = $application->form_of_address?->value ?? 'formal'; @endphp

<x-mail::message>

{{-- Header --}}
<x-slot:header></x-slot:header>

{{-- Content --}}
@if ($application->contact_name)
{{ __("mail.application.{$form}.salutation_named", ['name' => $application->contact_name]) }}<br>
@else
{{ __("mail.application.{$form}.salutation_generic") }}<br>
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
