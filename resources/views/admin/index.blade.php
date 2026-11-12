@extends('admin.layouts.default')

@section('title')
    {{ __('admin_dashboard.page_title') }}
    @parent
@stop

@section('header_styles')
    <style>
        .dashboard-ui {
            min-height: calc(100vh - 64px);
            padding: clamp(22px, 3vw, 40px);
            color: #25344d;
            background:
                radial-gradient(circle at 96% 3%, rgba(92, 132, 209, .09), transparent 27%),
                #f3f6fa;
        }

        .dashboard-hero {
            display: flex;
            max-width: 1380px;
            margin: 0 auto 24px;
            padding: clamp(24px, 3vw, 38px);
            background: #fff;
            border: 1px solid #e0e6ef;
            border-left: 5px solid #5c84d1;
            border-radius: 18px;
            box-shadow: 0 13px 36px rgba(30, 41, 59, .07);
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .dashboard-eyebrow {
            display: block;
            margin-bottom: 7px;
            color: #557cc5;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .dashboard-title {
            margin: 0;
            color: #25344d;
            font-size: clamp(27px, 3vw, 39px);
            font-weight: 800;
            line-height: 1.15;
        }

        .dashboard-subtitle {
            margin: 10px 0 0;
            color: #718096;
            font-size: 14px;
        }

        .dashboard-hero__icon {
            display: inline-flex;
            width: 64px;
            height: 64px;
            color: #567fca;
            background: #edf3fc;
            border: 1px solid #d9e4f5;
            border-radius: 17px;
            font-size: 24px;
            align-items: center;
            justify-content: center;
            flex: 0 0 64px;
        }

        .dashboard-stats {
            display: grid;
            max-width: 1380px;
            margin: 0 auto;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .dashboard-stat {
            position: relative;
            overflow: hidden;
            min-height: 190px;
            padding: 25px;
            background: #fff;
            border: 1px solid #e0e6ef;
            border-radius: 17px;
            box-shadow: 0 13px 34px rgba(30, 41, 59, .07);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .dashboard-stat::before {
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            content: "";
            background: var(--stat-accent);
        }

        .dashboard-stat::after {
            position: absolute;
            right: -46px;
            bottom: -62px;
            width: 155px;
            height: 155px;
            content: "";
            background: var(--stat-soft);
            border-radius: 50%;
        }

        .dashboard-stat:hover {
            box-shadow: 0 18px 42px rgba(30, 41, 59, .11);
            transform: translateY(-3px);
        }

        .dashboard-stat--users {
            --stat-accent: #4f78c4;
            --stat-soft: #edf3fc;
        }

        .dashboard-stat--applications {
            --stat-accent: #6576ad;
            --stat-soft: #eff1f9;
        }

        .dashboard-stat__top {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .dashboard-stat__label {
            margin: 0;
            color: #5d6b80;
            font-size: 13px;
            font-weight: 750;
        }

        .dashboard-stat__number {
            display: block;
            margin-top: 11px;
            color: #23324a;
            font-size: 39px;
            font-weight: 800;
            line-height: 1;
        }

        .dashboard-stat__icon {
            display: inline-flex;
            width: 50px;
            height: 50px;
            color: var(--stat-accent);
            background: var(--stat-soft);
            border-radius: 13px;
            font-size: 20px;
            align-items: center;
            justify-content: center;
            flex: 0 0 50px;
        }

        .dashboard-stat__description {
            position: relative;
            z-index: 1;
            max-width: 75%;
            margin: 22px 0 0;
            color: #8793a5;
            font-size: 12px;
            line-height: 1.5;
        }

        @media (max-width: 1000px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

        }

        @media (max-width: 650px) {
            .dashboard-ui {
                padding: 15px;
            }

            .dashboard-hero {
                align-items: flex-start;
            }

            .dashboard-hero__icon {
                display: none;
            }

            .dashboard-stats {
                grid-template-columns: 1fr;
            }

        }
    </style>
@stop

@section('content')
    <main class="dashboard-ui">
        <header class="dashboard-hero">
            <div>
                <span class="dashboard-eyebrow">{{ __('admin_dashboard.eyebrow') }}</span>
                <h1 class="dashboard-title">{{ __('admin_dashboard.welcome') }}</h1>
                <p class="dashboard-subtitle">{{ __('admin_dashboard.subtitle') }}</p>
            </div>
            <div class="dashboard-hero__icon" aria-hidden="true">
                <i class="fas fa-chart-bar"></i>
            </div>
        </header>

        <section class="dashboard-stats">
            <article class="dashboard-stat dashboard-stat--users">
                <div class="dashboard-stat__top">
                    <div>
                        <h2 class="dashboard-stat__label">{{ __('admin_dashboard.total_users') }}</h2>
                        <span class="dashboard-stat__number">{{ number_format($totalUsers) }}</span>
                    </div>
                    <span class="dashboard-stat__icon" aria-hidden="true">
                        <i class="fas fa-users"></i>
                    </span>
                </div>
                <p class="dashboard-stat__description">{{ __('admin_dashboard.users_description') }}</p>
            </article>

            <article class="dashboard-stat dashboard-stat--applications">
                <div class="dashboard-stat__top">
                    <div>
                        <h2 class="dashboard-stat__label">{{ __('admin_dashboard.total_applications') }}</h2>
                        <span class="dashboard-stat__number">{{ number_format($totalApplications) }}</span>
                    </div>
                    <span class="dashboard-stat__icon" aria-hidden="true">
                        <i class="fas fa-briefcase"></i>
                    </span>
                </div>
                <p class="dashboard-stat__description">{{ __('admin_dashboard.applications_description') }}</p>
            </article>

        </section>

    </main>
@stop
