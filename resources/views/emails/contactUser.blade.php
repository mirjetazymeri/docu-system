
@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            Josh Admin
        @endcomponent
    @endslot
    {{-- Body --}}

 # Hello {{ $data['contact-name'] }}

Welcome to SiteNameHere! We have received your details.<br />
{{ __('ui.the_provided_details_are') }}<br />
**{{ __('ui.name') }}:** {{ $data['contact-name'] }}<br />
**{{ __('ui.email') }}:** {{ $data['contact-email'] }}<br />
**Message :** {{ $data['contact-msg'] }}

Thank you for Contacting SiteNameHere! We will revert you shortly.

Best regards,

    {{-- Footer --}}
    @slot('footer')
    @component('mail::footer')
    &copy; 2017 All Copy right received
@endcomponent
@endslot
@endcomponent
