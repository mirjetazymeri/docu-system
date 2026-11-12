
@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            Josh Admin
        @endcomponent
    @endslot

    {{-- Body --}}
# Hello  {!! $user['user_name'] !!},<br>

{{ __('ui.welcome_to_sitenamehere_please_click_on_the_following_link_to_confirm_your_sitenamehere_account') }}<br />
@component('mail::button', ['url' =>  $user['activationUrl']  ])
    Activate Account
@endcomponent


    Thanks,

    {{-- Footer --}}
    @slot('footer')
    @component('mail::footer')
    &copy; 2018 All Copy right received
@endcomponent
@endslot
@endcomponent