@component('mail::message')

Hello {{$user->first_name}},

@component('mail::button',['url'=>route('verifyEmail',$user->email_verification_code)])
Click here to verify your email address.
@endcomponent

<p>Or copy paste the following link on your web browser to verify email address.</p>

<p><a href="{{route('verifyEmail',$user->email_verification_code)}}">{{route('verifyEmail',$user->email_verification_code)}}</a></p>

Thanks, <br>
{{config('app.name')}}
@endcomponent
