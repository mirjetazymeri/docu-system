<ul id="menu" class="page-sidebar-menu">

    @if(Sentinel::inRole('admin'))
    <li {!! (Request::is('admin') || Request::is('admin/index1') ? 'class="active"' : '' ) !!}>
        <a href="#dashboardSubmenu" data-toggle="collapse" aria-expanded="{{ Request::is('admin') || Request::is('admin/index1') ? 'true' : 'false' }}" class="dropdown-toggle">
            <i class="livicon" data-name="dashboard" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
            <span class="title">{{ __('words.dashboard') }}</span>
        </a>
        <ul class="collapse list-unstyled" id="dashboardSubmenu">
            <li {!! (Request::is('admin') ? 'class="active"' : '' ) !!}>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="livicon" data-name="dashboard" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
            <li {!! (Request::is('admin/index1') ? 'class="active"' : '' ) !!}>
                <a href="{{ URL::to('admin/index1') }}">
                    <i class="livicon" data-name="dashboard" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
                    {{ __('words.dashboard') }}
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- Company Management is intentionally hidden from the sidebar. --}}

    @if(Sentinel::getUser()->hasAccess('users.view') || Sentinel::getUser()->hasAccess('access.view'))
        @php
            $administrationIsActive =
                Request::is('admin/users*') ||
                Request::is('admin/deleted_users') ||
                Request::is('admin/bulk_import_users') ||
                Request::is('admin/user_profile') ||
                Request::is('admin/access-levels*') ||
                Request::is('admin/translations*');
        @endphp
        <li {!! $administrationIsActive ? 'class="active"' : '' !!}>
            <a href="#administrationSubmenu" data-toggle="collapse"
                aria-expanded="{{ $administrationIsActive ? 'true' : 'false' }}"
                class="dropdown-toggle">
                <i class="livicon" data-name="gears" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
                <span class="title">{{ __('ui.administration') }}</span>
                <span class="fa arrow"></span>
            </a>
            <ul class="collapse list-unstyled {{ $administrationIsActive ? 'show' : '' }}"
                id="administrationSubmenu">
                @if(Sentinel::getUser()->hasAccess('users.view'))
                    <li {!! Request::is('admin/users*') || Request::is('admin/deleted_users') || Request::is('admin/bulk_import_users') || Request::is('admin/user_profile') ? 'class="active"' : '' !!}>
                        <a href="{{ URL::to('admin/users') }}">
                            <i class="fa fa-users"></i>
                            {{ __('words.users') }}
                        </a>
                    </li>
                @endif
                @if(Sentinel::getUser()->hasAccess('access.view'))
                    <li {!! Request::is('admin/access-levels*') ? 'class="active"' : '' !!}>
                        <a href="{{ URL('admin/access-levels') }}">
                            <i class="fa fa-unlock-alt"></i>
                            {{ __('ui.access_levels') }}
                        </a>
                    </li>
                    <li {!! Request::is('admin/translations*') ? 'class="active"' : '' !!}>
                        <a href="{{ URL('admin/translations') }}">
                            <i class="fa fa-language"></i>
                            {{ __('words.translations') }}
                        </a>
                    </li>
                @endif
            </ul>
        </li>
    @endif

    {{-- Reports and Activity Log are intentionally hidden from the sidebar. --}}

<li {!! (Request::is('applications*') || Request::is('admin/applications*') || Request::is('admin/appcategories*') ? 'class="active"' : '' ) !!}>
    <a href="#applicationsSubmenu" data-toggle="collapse"
        aria-expanded="{{ Request::is('applications*') || Request::is('admin/applications*') || Request::is('admin/appcategories*') ? 'true' : 'false' }}"
        class="dropdown-toggle">
        <i class="livicon" data-name="briefcase" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
        <span class="title">{{ __('words.applications') }}</span>
        <span class="fa arrow"></span>
    </a>
    <ul class="collapse list-unstyled {{ Request::is('applications*') || Request::is('admin/applications*') || Request::is('admin/appcategories*') ? 'show' : '' }}"
        id="applicationsSubmenu">
        @php
            $sidebarUser = Sentinel::getUser();
            $sidebarIsDocumentationWorker = !Sentinel::inRole('admin')
                && $sidebarUser->accessLevels()
                    ->where('access_levels.name', 'Documentation Worker')
                    ->exists();
            $selectedApplicationScope = Request::query('scope', 'all');
        @endphp
        @if($sidebarIsDocumentationWorker)
            <li {!! Request::is('applications*') && $selectedApplicationScope !== 'assigned' ? 'class="active"' : '' !!}>
                <a href="{{ route('applications.index', ['scope' => 'all']) }}">
                    <i class="fa fa-th-large"></i>
                    {{ __('documentation_workers.all_applications') }}
                </a>
            </li>
            <li {!! Request::is('applications*') && $selectedApplicationScope === 'assigned' ? 'class="active"' : '' !!}>
                <a href="{{ route('applications.index', ['scope' => 'assigned']) }}">
                    <i class="fa fa-user-check"></i>
                    {{ __('documentation_workers.my_applications') }}
                </a>
            </li>
        @else
            <li {!! ((Request::is('applications*') || Request::is('admin/applications*')) && !Request::routeIs('applications.status') ? 'class="active"' : '' ) !!}>
                <a href="{{ route('applications.index') }}">
                    <i class="fa fa-list"></i>
                    {{ __('words.applications') }}
                </a>
            </li>
        @endif
        @if(Sentinel::inRole('admin'))
            <li {!! (Request::is('admin/appcategories*') ? 'class="active"' : '' ) !!}>
                <a href="{{ route('admin.appcategories.index') }}">
                    <i class="fa fa-tags"></i>
                    {{ __('words.app_categories') }}
                </a>
            </li>
            <li {!! Request::routeIs('applications.status') ? 'class="active"' : '' !!}>
                <a href="{{ route('applications.status') }}">
                    <i class="fa fa-chart-bar"></i>
                    {{ __('application_status.menu_title') }}
                </a>
            </li>
        @endif
    </ul>
</li>

@php
    $sidebarCanManageApplicationTesting = Sentinel::inRole('admin')
        && Sentinel::getUser()->hasAccess('audits.view');
    $applicationTestingIsActive = Request::is('admin/application_testing*')
        || Request::is('admin/application-testing*')
        || Request::is('testing-issues*');
@endphp
@if($sidebarCanManageApplicationTesting || $sidebarIsDocumentationWorker)
    <li {!! $applicationTestingIsActive ? 'class="active"' : '' !!}>
        <a href="#applicationTestingSubmenu" data-toggle="collapse"
            aria-expanded="{{ $applicationTestingIsActive ? 'true' : 'false' }}"
            class="dropdown-toggle">
            <i class="fa fa-vial"></i>
            <span class="title">{{ __('words.testing.app') }}</span>
            <span class="fa arrow"></span>
        </a>
        <ul class="collapse list-unstyled {{ $applicationTestingIsActive ? 'show' : '' }}"
            id="applicationTestingSubmenu">
            @if($sidebarCanManageApplicationTesting)
                <li {!! Request::is('admin/application_testing*') || Request::is('admin/application-testing*') ? 'class="active"' : '' !!}>
                    <a href="{{ route('admin.application_testing.index') }}">
                        <i class="fa fa-clipboard-list"></i>
                        {{ __('testing_issues.testing_records') }}
                    </a>
                </li>
            @endif
            <li {!! Request::is('testing-issues*') ? 'class="active"' : '' !!}>
                <a href="{{ route('testing-issues.index') }}">
                    <i class="fa fa-clipboard-check"></i>
                    {{ __('testing_issues.title') }}
                </a>
            </li>
        </ul>
    </li>
@endif

@if(Sentinel::inRole('admin'))
<li class="treeview {!! (Request::is('admin/audits*') || Request::is('admin/audit-titles*') ? 'active' : '') !!}">
    <a href="#">
        <i class="livicon" data-name="folder-open" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
        <span>{{ __('words.audit_field')}}</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>
    <ul class="treeview-menu">
        <li {!! (Request::is('admin/audits*') ? 'class="active"' : '') !!}>
            <a href="{{ route('admin.audits.index') }}">
                <i class="livicon" data-name="briefcase" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
                {{ __('words.audits') }}
            </a>
        </li>
        <li {!! (Request::is('admin/audit-titles*') ? 'class="active"' : '') !!}>
            <a href="{{ route('admin.audit-titles.index') }}">
                <i class="livicon" data-name="list" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
                {{ __('words.audit_titles') }}
            </a>
        </li>
    </ul>
</li>



<li class="treeview {!! (Request::is('admin/brands*') || Request::is('admin/device-types*') || Request::is('admin/assets-area*') ? 'active' : '') !!}">
    <a href="#">
        <i class="livicon" data-name="settings" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
        <span>  {{ __('words.assets_area') }}</span>
        <i class="fa fa-angle-left pull-right"></i>
    </a>
    <ul class="treeview-menu">
        <li {!! (Request::is('admin/brands*') ? 'class="active"' : '') !!}>
            <a href="{{ route('admin.brands.index') }}">
                <i class="livicon" data-name="briefcase" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
                {{ __('words.brands') }}
            </a>
        </li>

        <li {!! (Request::is('admin/device-types*') ? 'class="active"' : '') !!}>
            <a href="{{ route('admin.device-types.index') }}">
                <i class="livicon" data-name="list" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
              {{ __('words.device_types') }}
            </a>
        </li>

        <li {!! (Request::is('admin/assets-area*') ? 'class="active"' : '') !!}>
            <a href="{{ route('admin.assets-areas.index') }}">
                <i class="livicon" data-name="list" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
                {{ __('words.assets') }}
            </a>
        </li>
    </ul>
</li>


    {{-- <li {!! (Request::is('admin/generator_builder') ? 'class="active"' : '' ) !!}>
        <a href="{{ URL('admin/generator_builder') }}">
            <i class="livicon" data-name="shield" data-size="18" data-c="#FFFFFF" data-hc="#FFFFFF" data-loop="true"></i>
            CRUD Generator
        </a>
    </li> --}}
    <!-- Menus generated by CRUD generator -->
    @include('admin/layouts/menu')
@endif

@if(Sentinel::inRole('admin'))
    <li {!! Request::is('admin/documentation-workers*') ? 'class="active"' : '' !!}>
        <a href="{{ route('admin.documentation-workers.index') }}">
            <i class="fa fa-history"></i>
            <span class="title">{{ __('documentation_workers.section_title') }}</span>
        </a>
    </li>
@endif
</ul>
