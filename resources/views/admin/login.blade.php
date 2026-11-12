<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/blank-favicon.svg') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin_login.page_title') }}</title>
    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/all.css') }}">
    <style>
        :root {
            --auth-navy: #18243a;
            --auth-navy-dark: #111a2b;
            --auth-blue: #5c84d1;
            --auth-blue-dark: #476eb9;
            --auth-text: #25344d;
            --auth-muted: #708096;
            --auth-border: #dce3ed;
            --auth-bg: #eef2f7;
            --auth-danger: #a93631;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--auth-text);
            background:
                radial-gradient(circle at 8% 12%, rgba(92, 132, 209, .14), transparent 28%),
                radial-gradient(circle at 92% 88%, rgba(24, 36, 58, .09), transparent 30%),
                var(--auth-bg);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .auth-page {
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }

        .auth-shell {
            display: grid;
            width: 100%;
            max-width: 980px;
            min-height: 590px;
            grid-template-columns: minmax(320px, .92fr) minmax(420px, 1.08fr);
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(203, 213, 225, .85);
            border-radius: 24px;
            box-shadow: 0 28px 75px rgba(30, 41, 59, .17);
        }

        .auth-brand {
            position: relative;
            display: flex;
            overflow: hidden;
            padding: 52px 44px;
            color: #fff;
            background: linear-gradient(145deg, var(--auth-navy), var(--auth-navy-dark));
            flex-direction: column;
            justify-content: space-between;
        }

        .auth-brand::before,
        .auth-brand::after {
            position: absolute;
            content: "";
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 50%;
        }

        .auth-brand::before {
            top: -125px;
            right: -125px;
            width: 330px;
            height: 330px;
        }

        .auth-brand::after {
            right: -65px;
            bottom: -105px;
            width: 245px;
            height: 245px;
            background: rgba(92, 132, 209, .08);
        }

        .auth-logo,
        .auth-brand__content,
        .auth-brand__footer {
            position: relative;
            z-index: 1;
        }

        .auth-logo {
            width: 145px;
            height: auto;
        }

        .auth-brand__eyebrow {
            display: block;
            margin-bottom: 13px;
            color: #95b5ef;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.6px;
            text-transform: uppercase;
        }

        .auth-brand h1 {
            max-width: 330px;
            margin: 0;
            font-size: 35px;
            font-weight: 700;
            line-height: 1.18;
        }

        .auth-brand p {
            max-width: 340px;
            margin: 20px 0 0;
            color: #c6d0df;
            font-size: 15px;
            line-height: 1.7;
        }

        .auth-brand__footer {
            color: #8492a8;
            font-size: 12px;
        }

        .auth-form-panel {
            position: relative;
            display: flex;
            padding: 50px clamp(36px, 6vw, 70px);
            background: #fff;
            align-items: center;
        }

        .auth-language {
            position: absolute;
            top: 24px;
            right: 28px;
            z-index: 5;
        }

        .auth-language summary {
            display: inline-flex;
            min-width: 105px;
            height: 38px;
            padding: 0 12px;
            color: #526176;
            background: #f8fafc;
            border: 1px solid var(--auth-border);
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            list-style: none;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .auth-language summary::-webkit-details-marker {
            display: none;
        }

        .auth-language summary:hover,
        .auth-language[open] summary {
            color: var(--auth-blue-dark);
            background: #edf3fc;
            border-color: #bdcce5;
        }

        .auth-language__menu {
            position: absolute;
            top: 44px;
            right: 0;
            min-width: 150px;
            padding: 6px;
            background: #fff;
            border: 1px solid var(--auth-border);
            border-radius: 10px;
            box-shadow: 0 14px 30px rgba(30, 41, 59, .15);
        }

        .auth-language__menu a {
            display: flex;
            padding: 9px 10px;
            color: #4c5b70;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 650;
            text-decoration: none;
            align-items: center;
            justify-content: space-between;
        }

        .auth-language__menu a:hover,
        .auth-language__menu a[aria-current="true"] {
            color: var(--auth-blue-dark);
            background: #edf3fc;
        }

        .auth-language__code {
            color: #8995a6;
            font-size: 9px;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 420px;
            margin: auto;
        }

        .auth-form__eyebrow {
            display: block;
            margin-bottom: 8px;
            color: var(--auth-blue-dark);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .auth-form h2 {
            margin: 0;
            color: var(--auth-text);
            font-size: 31px;
            font-weight: 750;
        }

        .auth-form__subtitle {
            margin: 9px 0 28px;
            color: var(--auth-muted);
            font-size: 14px;
        }

        .auth-notifications {
            margin-bottom: 18px;
        }

        .auth-notifications:empty {
            display: none;
        }

        .auth-notifications .alert {
            margin-bottom: 12px;
            border: 0;
            border-radius: 10px;
            font-size: 13px;
        }

        .auth-field {
            margin-bottom: 19px;
        }

        .auth-field label {
            display: block;
            margin-bottom: 7px;
            color: #35445c;
            font-size: 13px;
            font-weight: 700;
        }

        .auth-input {
            position: relative;
        }

        .auth-input > i {
            position: absolute;
            top: 50%;
            left: 15px;
            color: #7c8ca3;
            font-size: 14px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .auth-input input {
            width: 100%;
            height: 49px;
            padding: 0 45px 0 43px;
            color: #29384f;
            background: #f9fbfd;
            border: 1px solid var(--auth-border);
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .auth-input input::placeholder {
            color: #9aa7b8;
        }

        .auth-input input:focus {
            background: #fff;
            border-color: #7898d4;
            box-shadow: 0 0 0 4px rgba(92, 132, 209, .12);
        }

        .auth-input--error input {
            border-color: #d88b87;
        }

        .auth-password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            width: 34px;
            height: 34px;
            padding: 0;
            color: #718096;
            background: transparent;
            border: 0;
            border-radius: 7px;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .auth-password-toggle:hover {
            color: var(--auth-blue-dark);
            background: #edf3fc;
        }

        .auth-error {
            display: block;
            margin-top: 6px;
            color: var(--auth-danger);
            font-size: 12px;
        }

        .auth-options {
            display: flex;
            margin: 2px 0 24px;
            align-items: center;
            justify-content: space-between;
        }

        .auth-remember {
            display: inline-flex;
            margin: 0;
            color: #526176;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            align-items: center;
            gap: 9px;
        }

        .auth-remember input {
            width: 17px;
            height: 17px;
            margin: 0;
            accent-color: var(--auth-blue);
        }

        .auth-submit {
            display: inline-flex;
            width: 100%;
            min-height: 49px;
            color: #fff;
            background: var(--auth-blue);
            border: 0;
            border-radius: 10px;
            box-shadow: 0 10px 22px rgba(71, 110, 185, .22);
            font-size: 14px;
            font-weight: 750;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            gap: 9px;
            transition: background .2s ease, transform .2s ease, box-shadow .2s ease;
        }

        .auth-submit:hover {
            background: var(--auth-blue-dark);
            box-shadow: 0 12px 26px rgba(71, 110, 185, .28);
            transform: translateY(-1px);
        }

        .auth-submit:focus {
            outline: 3px solid rgba(92, 132, 209, .22);
            outline-offset: 2px;
        }

        .auth-security {
            margin: 22px 0 0;
            color: #8a96a7;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 820px) {
            .auth-page {
                padding: 20px;
            }

            .auth-shell {
                max-width: 540px;
                min-height: auto;
                grid-template-columns: 1fr;
            }

            .auth-brand {
                min-height: 205px;
                padding: 32px;
            }

            .auth-brand__content {
                margin-top: 35px;
            }

            .auth-brand h1 {
                font-size: 26px;
            }

            .auth-brand p,
            .auth-brand__footer {
                display: none;
            }

            .auth-form-panel {
                padding: 72px 32px 42px;
            }
        }

        @media (max-width: 480px) {
            .auth-page {
                padding: 0;
                align-items: stretch;
            }

            .auth-shell {
                border: 0;
                border-radius: 0;
            }

            .auth-brand {
                min-height: 175px;
                padding: 27px 24px;
            }

            .auth-logo {
                width: 125px;
            }

            .auth-brand__content {
                margin-top: 25px;
            }

            .auth-brand h1 {
                font-size: 23px;
            }

            .auth-form-panel {
                padding: 72px 24px 42px;
            }

            .auth-language {
                top: 20px;
                right: 24px;
            }

            .auth-form h2 {
                font-size: 27px;
            }
        }
    </style>
</head>
<body>
    <main class="auth-page">
        <section class="auth-shell" aria-label="{{ __('admin_login.panel_aria') }}">
            <aside class="auth-brand">
                <img src="{{ asset('frontend/assets/images/logo-prog.png') }}"
                    alt="{{ __('admin_login.brand_alt') }}" class="auth-logo">

                <div class="auth-brand__content">
                    <span class="auth-brand__eyebrow">{{ __('admin_login.brand_eyebrow') }}</span>
                    <h1>{{ __('admin_login.brand_title') }}</h1>
                    <p>{{ __('admin_login.brand_description') }}</p>
                </div>

                <div class="auth-brand__footer">
                    {{ __('admin_login.copyright', ['year' => now()->year]) }}
                </div>
            </aside>

            <div class="auth-form-panel">
                <details class="auth-language">
                    <summary aria-label="{{ __('admin_login.language_label') }}">
                        <span><i class="fas fa-globe" aria-hidden="true"></i></span>
                        <span>{{ LaravelLocalization::getCurrentLocaleNative() }}</span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </summary>
                    <div class="auth-language__menu">
                        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            <a rel="alternate" hreflang="{{ $localeCode }}"
                                href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                aria-current="{{ app()->getLocale() === $localeCode ? 'true' : 'false' }}">
                                <span>{{ $properties['native'] }}</span>
                                <span class="auth-language__code">{{ $localeCode }}</span>
                            </a>
                        @endforeach
                    </div>
                </details>

                <div class="auth-form-wrap">
                    <div class="auth-notifications">
                        @include('notifications')
                    </div>

                    <form action="{{ route('signin') }}" method="post" id="login_form" class="auth-form">
                        @csrf

                        <span class="auth-form__eyebrow">{{ __('admin_login.form_eyebrow') }}</span>
                        <h2>{{ __('admin_login.welcome') }}</h2>
                        <p class="auth-form__subtitle">{{ __('admin_login.subtitle') }}</p>

                        <div class="auth-field">
                            <label for="email">{{ __('admin_login.email_label') }}</label>
                            <div class="auth-input {{ $errors->has('email') ? 'auth-input--error' : '' }}">
                                <i class="fas fa-envelope" aria-hidden="true"></i>
                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    placeholder="{{ __('admin_login.email_placeholder') }}"
                                    autocomplete="email" required autofocus>
                            </div>
                            @error('email')
                                <span class="auth-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="auth-field">
                            <label for="password">{{ __('admin_login.password_label') }}</label>
                            <div class="auth-input {{ $errors->has('password') ? 'auth-input--error' : '' }}">
                                <i class="fas fa-lock" aria-hidden="true"></i>
                                <input id="password" name="password" type="password"
                                    placeholder="{{ __('admin_login.password_placeholder') }}"
                                    autocomplete="current-password" required>
                                <button type="button" class="auth-password-toggle" id="password-toggle"
                                    aria-label="{{ __('admin_login.show_password') }}" aria-pressed="false">
                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="auth-error" role="alert">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="auth-options">
                            <label class="auth-remember" for="remember-me">
                                <input type="checkbox" name="remember-me" id="remember-me" value="1"
                                    @checked(old('remember-me'))>
                                <span>{{ __('admin_login.remember_me') }}</span>
                            </label>
                        </div>

                        <button type="submit" class="auth-submit">
                            <span>{{ __('admin_login.submit') }}</span>
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </button>

                        <p class="auth-security">
                            <i class="fas fa-shield-alt" aria-hidden="true"></i>
                            {{ __('admin_login.security_notice') }}
                        </p>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('password-toggle');
            const password = document.getElementById('password');
            const showPasswordLabel = @json(__('admin_login.show_password'));
            const hidePasswordLabel = @json(__('admin_login.hide_password'));

            toggle.addEventListener('click', function () {
                const showPassword = password.type === 'password';
                password.type = showPassword ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
                toggle.setAttribute(
                    'aria-label',
                    showPassword ? hidePasswordLabel : showPasswordLabel
                );
                toggle.querySelector('i').className = showPassword
                    ? 'fas fa-eye-slash'
                    : 'fas fa-eye';
            });
        });
    </script>
@include('admin.partials.widget-localization')
</body>
</html>
