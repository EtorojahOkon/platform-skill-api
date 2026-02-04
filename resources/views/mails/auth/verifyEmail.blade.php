<x-mail::message>
# Hello {{ $name }},
 
Use the 4-digit code below to verify your email address.

{{ $otp }}

This code will expire in {{ $expiryTime }}.
 
<br>
{{ config('app.name') }}
</x-mail::message>