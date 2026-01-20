@component('mail::message')
# Verify Your Email Address

Hi {{ $user->name }},

Thank you for creating your AneWP account! To get started, please verify your email address by clicking the button below.

@component('mail::button', ['url' => \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => hash('sha256', $user->email)])])
Verify Email Address
@endcomponent

If you didn't create this account, you can ignore this email. This link expires in 60 minutes.

Thanks,<br>
{{ config('app.name') }}

@component('mail::footer')
@slot('subcopy')
If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:
[{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => hash('sha256', $user->email)]) }}]({{ \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => hash('sha256', $user->email)]) }})
@endslot
@endcomponent
@endcomponent
