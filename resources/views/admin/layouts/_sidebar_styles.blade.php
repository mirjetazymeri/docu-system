<style>
    /* Modern admin sidebar */
    .skin-josh {
        --sidebar-bg: #172033;
        --sidebar-surface: #202b42;
        --sidebar-hover: #293750;
        --sidebar-active: #4f78c4;
        --sidebar-text: #dfe7f3;
        --sidebar-muted: #9cabc0;
        --sidebar-border: rgba(255, 255, 255, .07);
    }

    .skin-josh > .header {
        min-height: 64px;
        background: var(--sidebar-bg) !important;
        border-bottom: 1px solid var(--sidebar-border);
        box-shadow: 0 5px 22px rgba(15, 23, 42, .12);
    }

    .skin-josh > .header .logo {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 64px;
        background: var(--sidebar-bg) !important;
        border-right: 1px solid var(--sidebar-border);
    }

    .skin-josh > .header .logo img {
        display: block;
        width: 132px;
        max-height: 38px;
        object-fit: contain;
    }

    .skin-josh > .header .navbar {
        height: 64px;
        min-height: 64px;
        background: linear-gradient(90deg, var(--sidebar-bg), #202d46) !important;
    }

    .skin-josh > .header .navbar .sidebar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin: 11px 0 11px 12px;
        padding: 0;
        color: #fff;
        background: var(--sidebar-surface) !important;
        border: 1px solid var(--sidebar-border);
        border-radius: 11px;
        transition: background-color .18s ease, transform .18s ease;
    }

    .skin-josh > .header .navbar .sidebar-toggle:hover {
        background: var(--sidebar-hover) !important;
        transform: translateY(-1px);
    }

    .skin-josh > .header .responsive_nav {
        margin: 0;
        filter: brightness(0) invert(1);
        opacity: .9;
    }

    .skin-josh > .header .navbar-right {
        height: 64px;
    }

    .skin-josh > .header .navbar-nav {
        display: flex;
        align-items: center;
        height: 64px;
        gap: 6px;
    }

    .skin-josh > .header .navbar-nav > li {
        display: flex;
        align-items: center;
        height: 100%;
    }

    .skin-josh > .header .navbar-nav > li > a,
    .skin-josh > .header #navbarDropdownLang {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
        margin: 0;
        padding: 7px 12px;
        color: var(--sidebar-text) !important;
        background: transparent !important;
        border-radius: 11px;
        font-size: 14px;
        font-weight: 700;
        transition: background-color .18s ease, color .18s ease;
    }

    .skin-josh > .header .navbar-nav > li > a:hover,
    .skin-josh > .header .navbar-nav > li > a:focus,
    .skin-josh > .header .navbar-nav > .open > a,
    .skin-josh > .header #navbarDropdownLang:hover {
        color: #fff !important;
        background: var(--sidebar-hover) !important;
    }

    .skin-josh > .header .navbar-nav > .user-menu > a img {
        width: 36px !important;
        height: 36px !important;
        margin-right: 0;
        border: 2px solid rgba(255, 255, 255, .72);
        box-shadow: 0 3px 10px rgba(0, 0, 0, .2);
        object-fit: cover;
    }

    .skin-josh > .header .user_name_max {
        margin: 0;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
    }

    .skin-josh > .header .riot > div {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .skin-josh > .header .dropdown-menu {
        overflow: hidden;
        min-width: 210px;
        margin-top: 7px;
        padding: 7px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 13px;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .18);
    }

    .skin-josh > .header .dropdown-menu .dropdown-item,
    .skin-josh > .header .dropdown-menu > li > a {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 11px;
        color: #334155 !important;
        background: transparent;
        border-radius: 8px;
        font-size: 13px;
    }

    .skin-josh > .header .dropdown-menu .dropdown-item:hover,
    .skin-josh > .header .dropdown-menu > li > a:hover {
        color: #23457d !important;
        background: #edf3fc !important;
    }

    .skin-josh > .header .user-menu .user-header {
        margin-bottom: 5px;
        padding: 16px 12px;
        background: #edf3fc !important;
        border-radius: 9px;
    }

    .skin-josh > .header .user-menu .user-header .topprofiletext {
        color: #25344d !important;
    }

    .skin-josh .left-side,
    .skin-josh .sidebar,
    .skin-josh .page-sidebar,
    .skin-josh .sidebar-nav {
        background: var(--sidebar-bg) !important;
    }

    .skin-josh .left-side {
        border-right: 1px solid rgba(15, 23, 42, .16);
        box-shadow: 8px 0 28px rgba(15, 23, 42, .09);
    }

    .skin-josh .nav_icons {
        margin: 0 12px 12px;
        padding: 12px 8px;
        background: var(--sidebar-surface);
        border: 1px solid var(--sidebar-border);
        border-radius: 14px;
    }

    .skin-josh .sidebar_threeicons {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 4px;
    }

    .skin-josh .sidebar_threeicons li {
        border-radius: 10px;
        transition: background-color .18s ease;
    }

    .skin-josh .sidebar_threeicons li:hover {
        background: var(--sidebar-hover) !important;
    }

    .skin-josh .sidebar_threeicons li a {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 8px;
    }

    .skin-josh .page-sidebar-menu {
        padding: 0 10px 18px;
    }

    .skin-josh .sidebar .page-sidebar-menu > li {
        overflow: hidden;
        margin-bottom: 5px;
        background: transparent !important;
        border: 0 !important;
        border-radius: 11px;
    }

    .skin-josh .sidebar .page-sidebar-menu > li > a,
    .skin-josh .sidebar .page-sidebar-menu > li:hover > a {
        display: flex;
        align-items: center;
        min-height: 48px;
        padding: 11px 13px;
        color: var(--sidebar-text) !important;
        background: transparent !important;
        border-radius: 11px;
        font-family: Lato, sans-serif;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: .01em;
        transition: background-color .18s ease, color .18s ease, transform .18s ease;
    }

    .skin-josh .sidebar .page-sidebar-menu > li:hover > a {
        background: var(--sidebar-hover) !important;
        transform: translateX(2px);
    }

    .skin-josh .sidebar .page-sidebar-menu > li.active,
    .skin-josh .sidebar .page-sidebar-menu > li.active:hover {
        background: transparent !important;
    }

    .skin-josh .sidebar .page-sidebar-menu > li.active > a {
        color: #fff !important;
        background: linear-gradient(135deg, var(--sidebar-active), #668ed4) !important;
        box-shadow: 0 7px 18px rgba(79, 120, 196, .28);
    }

    .skin-josh .page-sidebar-menu > li > a > .livicon,
    .skin-josh .page-sidebar-menu > li > a > i:first-child {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        flex: 0 0 24px;
        width: 24px !important;
        margin-right: 11px;
        color: var(--sidebar-muted);
        text-align: center;
    }

    .skin-josh .page-sidebar-menu > li > a .title,
    .skin-josh .page-sidebar-menu > li > a > span:not(.fa) {
        flex: 1;
    }

    .skin-josh .page-sidebar-menu .arrow,
    .skin-josh .page-sidebar-menu .pull-right {
        margin: 0 0 0 auto;
        color: var(--sidebar-muted);
        font-size: 12px;
    }

    .skin-josh .sidebar li ul,
    .skin-josh .sidebar .sub-menu,
    .skin-josh .sidebar .treeview-menu,
    .skin-josh .sidebar .collapse {
        margin: 3px 0 7px !important;
        padding: 6px !important;
        background: rgba(8, 15, 28, .32) !important;
        border: 0 !important;
        border-radius: 10px;
    }

    .skin-josh .sidebar li ul li {
        margin: 2px 0;
        background: transparent !important;
        border-radius: 8px;
    }

    .skin-josh .sidebar li ul li a {
        display: flex;
        align-items: center;
        min-height: 39px;
        padding: 8px 10px 8px 15px !important;
        color: var(--sidebar-muted) !important;
        background: transparent !important;
        border-radius: 8px;
        font-size: 13px;
        transition: background-color .18s ease, color .18s ease;
    }

    .skin-josh .sidebar li ul li:hover > a,
    .skin-josh .sidebar li ul li.active > a,
    .skin-josh .left-side #active {
        color: #fff !important;
        background: var(--sidebar-hover) !important;
    }

    .skin-josh .sidebar li ul li i {
        margin-right: 8px;
        color: #7f9fd8;
    }

    .skin-josh .sidebar a:focus {
        outline: 2px solid rgba(116, 151, 216, .65);
        outline-offset: -2px;
    }

    /* Shared theme for legacy admin pages */
    .skin-josh .right-side {
        background: #f4f6fa;
    }

    .skin-josh .right-side > .content-header {
        min-height: 0;
        margin: 20px 24px 18px;
        padding: 18px 22px;
        background: #fff !important;
        border: 1px solid #e2e8f0;
        border-left: 4px solid var(--sidebar-active);
        border-radius: 14px;
        box-shadow: 0 7px 22px rgba(30, 41, 59, .06);
    }

    .skin-josh .right-side > .content-header > h1,
    .skin-josh .right-side > .content-header h1 {
        margin: 0;
        color: #25344d !important;
        font-size: clamp(1.45rem, 2.4vw, 2rem);
        font-weight: 800;
        text-align: left;
    }

    .skin-josh .right-side > .content-header .breadcrumb {
        top: 50%;
        right: 20px;
        margin: 0;
        color: #718096;
        transform: translateY(-50%);
    }

    .skin-josh .right-side .card,
    .skin-josh .right-side .panel,
    .skin-josh .right-side .box {
        border-color: #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(30, 41, 59, .07);
    }

    .skin-josh .right-side .card-header,
    .skin-josh .right-side .panel-heading,
    .skin-josh .right-side .box-header {
        color: #fff !important;
        background: linear-gradient(135deg, #25344d, #354a6c) !important;
        border-color: transparent !important;
    }

    .skin-josh .right-side .card-header:first-child {
        border-radius: 14px 14px 0 0;
    }

    .skin-josh .right-side .card-header *,
    .skin-josh .right-side .panel-heading *,
    .skin-josh .right-side .box-header * {
        color: inherit;
    }

    .skin-josh .right-side .btn-primary,
    .skin-josh .right-side .bg-primary,
    .skin-josh .right-side .fab {
        color: #fff !important;
        background-color: var(--sidebar-active) !important;
        border-color: var(--sidebar-active) !important;
    }

    .skin-josh .right-side .btn-primary:hover,
    .skin-josh .right-side .btn-primary:focus,
    .skin-josh .right-side .fab:hover {
        background-color: #4269b0 !important;
        border-color: #4269b0 !important;
    }

    .skin-josh .right-side .text-primary {
        color: #4168ae !important;
    }

    .skin-josh .right-side .form-control:focus {
        border-color: #7193cf;
        box-shadow: 0 0 0 .18rem rgba(79, 120, 196, .14);
    }

    .skin-josh .right-side .page-item.active .page-link,
    .skin-josh .right-side .pagination > .active > a {
        color: #fff;
        background-color: var(--sidebar-active);
        border-color: var(--sidebar-active);
    }

    .skin-josh .right-side .table thead th {
        color: #334155;
        background: #edf2f8;
        border-bottom-color: #d7e0ec;
    }

    @media (max-width: 767px) {
        .skin-josh > .header .logo {
            height: 64px;
        }
        .skin-josh > .header .navbar {
            height: 64px;
            min-height: 64px;
        }
        .skin-josh > .header .navbar-nav {
            gap: 0;
        }
        .skin-josh > .header #navbarDropdownLang,
        .skin-josh > .header .navbar-nav > li > a {
            padding-right: 8px;
            padding-left: 8px;
        }
        .skin-josh > .header .riot {
            display: none;
        }
        .skin-josh .left-side {
            box-shadow: 12px 0 32px rgba(15, 23, 42, .22);
        }
        .skin-josh .right-side > .content-header {
            margin: 12px;
            padding: 16px;
        }
        .skin-josh .right-side > .content-header .breadcrumb {
            position: static;
            float: none;
            margin-top: 8px;
            padding: 0;
            transform: none;
        }
    }
</style>
