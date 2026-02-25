<x-mail::message>
@php $recipient = $application->contact_name ?: $application->company_name; @endphp
{{ trim(__('Hello :name', ['name' => $recipient])) }},

{{ __('please find my application for the advertised position enclosed. You can view my complete application documents via the following link:') }}

<x-mail::button :url="config('app.frontend_url') . '/' . $application->public_id">{{ __('View Application') }}</x-mail::button>

{{ __('Kind regards,') }}
{{ $application->profile?->name ?? '' }}
@if ($application->profile?->phone)
{{ $application->profile->phone }}
@endif
@if ($application->profile?->email)
{{ $application->profile->email }}
@endif
</x-mail::message>
