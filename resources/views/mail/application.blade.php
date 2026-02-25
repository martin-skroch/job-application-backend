<x-mail::message>
@php $form = $application->form_of_address?->value ?? 'formal'; @endphp
@if ($application->contact_name)
{{ __("mail.application.{$form}.salutation_named", ['name' => $application->contact_name]) }}
@else
{{ __("mail.application.{$form}.salutation_generic") }}
@endif

{{ __("mail.application.{$form}.body") }}

<x-mail::button :url="config('app.frontend_url') . '/' . $application->public_id">
{{ __('mail.application.button') }}
</x-mail::button>

---

{{ __("mail.application.{$form}.closing") }}

**{{ $application->profile?->name ?? '' }}**
@if ($application->profile?->phone)
{{ $application->profile->phone }}
@endif
@if ($application->profile?->email)
{{ $application->profile->email }}
@endif
</x-mail::message>
