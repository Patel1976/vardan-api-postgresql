@component('mail::message')
# Forget Password Email

Hi {{ $name }},

There was a request to change your password!

If you did not make this request then please ignore this email.

Otherwise, please click this link to change your password:

@component('mail::button', ['url' => $domain])
Reset Password
@endcomponent

Or copy the password reset link into your browser:
[{{ $domain }}]({{ $domain }})

Thanks,
@endcomponent
