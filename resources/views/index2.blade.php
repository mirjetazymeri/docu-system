<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('welcome_page.page_title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/all.css') }}">
    <style>
        :root{--navy:#172033;--navy-light:#202d46;--blue:#5c84d1;--text:#25344d;--muted:#718096;--border:#dce3ed}
        *{box-sizing:border-box}html,body{min-height:100%}body{min-height:100vh;margin:0;color:var(--text);background:radial-gradient(circle at 10% 10%,rgba(92,132,209,.14),transparent 30%),radial-gradient(circle at 90% 90%,rgba(23,32,51,.09),transparent 28%),#eef2f7;font-family:"Segoe UI",Arial,sans-serif}
        .welcome-page{display:flex;min-height:100vh;padding:32px;align-items:center;justify-content:center}.welcome-card{position:relative;display:grid;overflow:hidden;width:100%;max-width:1120px;min-height:630px;background:#fff;border:1px solid rgba(203,213,225,.85);border-radius:26px;box-shadow:0 30px 80px rgba(30,41,59,.18);grid-template-columns:.92fr 1.08fr}
        .welcome-brand{position:relative;display:flex;overflow:hidden;padding:52px 46px;color:#fff;background:linear-gradient(145deg,var(--navy),#111a2b);flex-direction:column;justify-content:space-between}.welcome-brand:before,.welcome-brand:after{position:absolute;content:"";border:1px solid rgba(255,255,255,.08);border-radius:50%}.welcome-brand:before{top:-130px;right:-130px;width:350px;height:350px}.welcome-brand:after{right:-75px;bottom:-115px;width:270px;height:270px;background:rgba(92,132,209,.08)}
        .welcome-logo,.welcome-brand__content,.welcome-footer{position:relative;z-index:1}.welcome-logo{width:150px}.welcome-brand__content span{display:block;margin-bottom:14px;color:#95b5ef;font-size:11px;font-weight:800;letter-spacing:1.5px;text-transform:uppercase}.welcome-brand__content h1{max-width:390px;margin:0;font-size:38px;font-weight:750;line-height:1.18}.welcome-brand__content p{max-width:390px;margin:20px 0 0;color:#c6d0df;font-size:15px;line-height:1.75}.welcome-footer{color:#8997ac;font-size:11px}
        .welcome-content{position:relative;display:flex;padding:70px clamp(40px,6vw,76px);align-items:center}.welcome-content__inner{width:100%}.welcome-language{position:absolute;top:26px;right:30px;z-index:4}.welcome-language summary{display:flex;min-width:108px;height:39px;padding:0 12px;color:#526176;background:#f8fafc;border:1px solid var(--border);border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;list-style:none;align-items:center;justify-content:space-between;gap:8px}.welcome-language summary::-webkit-details-marker{display:none}.welcome-language[open] summary,.welcome-language summary:hover{color:#476eb9;background:#edf3fc}.welcome-language__menu{position:absolute;top:45px;right:0;min-width:150px;padding:6px;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 14px 30px rgba(30,41,59,.15)}.welcome-language__menu a{display:flex;padding:9px 10px;color:#4c5b70;border-radius:7px;font-size:12px;font-weight:650;text-decoration:none;justify-content:space-between}.welcome-language__menu a:hover,.welcome-language__menu a[aria-current=true]{color:#476eb9;background:#edf3fc}
        .welcome-heading{margin:0 0 13px;color:var(--text);font-size:29px;font-weight:800;line-height:1.25}.welcome-description{margin:0 0 28px;color:var(--muted);font-size:14px;line-height:1.7}.welcome-button{display:inline-flex;min-height:49px;padding:0 20px;color:#fff;background:var(--blue);border-radius:10px;box-shadow:0 11px 24px rgba(71,110,185,.25);font-size:14px;font-weight:750;text-decoration:none;align-items:center;gap:10px;transition:.2s}.welcome-button:hover{color:#fff;background:#476eb9;box-shadow:0 14px 30px rgba(71,110,185,.3);transform:translateY(-2px)}
        .welcome-features{display:grid;margin-top:34px;padding-top:25px;border-top:1px solid #e5eaf1;grid-template-columns:repeat(2,1fr);gap:14px}.welcome-feature{display:flex;padding:13px;background:#f8fafc;border:1px solid #e5eaf1;border-radius:11px;gap:10px}.welcome-feature>i{display:flex;width:34px;height:34px;color:#557cc5;background:#edf3fc;border-radius:9px;align-items:center;justify-content:center;flex:0 0 34px}.welcome-feature strong,.welcome-feature small{display:block}.welcome-feature strong{color:#33445d;font-size:12px}.welcome-feature small{margin-top:3px;color:#8591a2;font-size:10px;line-height:1.4}
        @media(max-width:820px){.welcome-page{padding:20px}.welcome-card{max-width:560px;min-height:auto;grid-template-columns:1fr}.welcome-brand{min-height:260px;padding:34px}.welcome-brand__content{margin-top:42px}.welcome-brand__content h1{font-size:27px}.welcome-brand__content p,.welcome-footer{display:none}.welcome-content{padding:78px 34px 42px}}
        @media(max-width:500px){.welcome-page{padding:0;align-items:stretch}.welcome-card{border:0;border-radius:0}.welcome-brand{min-height:225px;padding:28px 24px}.welcome-logo{width:130px}.welcome-brand__content{margin-top:32px}.welcome-brand__content h1{font-size:23px}.welcome-content{padding:75px 24px 38px}.welcome-language{top:20px;right:24px}.welcome-features{grid-template-columns:1fr}}
    </style>
</head>
<body>
<main class="welcome-page">
    <section class="welcome-card">
        <aside class="welcome-brand">
            <img src="{{ asset('frontend/assets/images/logo-prog.png') }}" alt="Documentation-system" class="welcome-logo">
            <div class="welcome-brand__content">
                <span>{{ __('welcome_page.eyebrow') }}</span>
                <h1>{{ __('welcome_page.title') }}</h1>
                <p>{{ __('welcome_page.description') }}</p>
            </div>
            <div class="welcome-footer">{{ __('welcome_page.copyright', ['year' => now()->year]) }}</div>
        </aside>

        <div class="welcome-content">
            <details class="welcome-language">
                <summary aria-label="{{ __('welcome_page.language_label') }}">
                    <i class="fas fa-globe"></i>
                    <span>{{ LaravelLocalization::getCurrentLocaleNative() }}</span>
                    <i class="fas fa-chevron-down"></i>
                </summary>
                <div class="welcome-language__menu">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                            hreflang="{{ $localeCode }}" rel="alternate"
                            aria-current="{{ app()->getLocale() === $localeCode ? 'true' : 'false' }}">
                            <span>{{ $properties['native'] }}</span><small>{{ strtoupper($localeCode) }}</small>
                        </a>
                    @endforeach
                </div>
            </details>

            <div class="welcome-content__inner">
                <h2 class="welcome-heading">{{ __('welcome_page.access_title') }}</h2>
                <p class="welcome-description">{{ __('welcome_page.access_description') }}</p>
                <a href="{{ url('/admin') }}" class="welcome-button">
                    {{ __('welcome_page.admin_button') }} <i class="fas fa-arrow-right"></i>
                </a>

                <div class="welcome-features">
                    <div class="welcome-feature">
                        <i class="fas fa-shield-alt"></i>
                        <div><strong>{{ __('welcome_page.secure_access') }}</strong><small>{{ __('welcome_page.secure_description') }}</small></div>
                    </div>
                    <div class="welcome-feature">
                        <i class="fas fa-layer-group"></i>
                        <div><strong>{{ __('welcome_page.organized_work') }}</strong><small>{{ __('welcome_page.organized_description') }}</small></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
</body>
</html>
