@component('mail::message')
# Verify Your Subscription  

Thank you for subscribing to {{ config('app.name') }}! Please confirm your email address to complete the subscription.  

@component('mail::button', ['url' => $verificationUrl])
Confirm Subscription
@endcomponent  

If you didn't subscribe, you can ignore this email.  

Thanks,<br>  
**{{ config('app.name') }} Team**  
@endcomponent
