@section('nav-main')
    <nav class="nav-main">
        <div class="nav-main-wrapper">
            <ul class="mt-sm">
                <li class="{{ current_class('dashboard') }}">
                    <a href="{{ route('statamic.cp.dashboard') }}">
                        <i>@svg('charts')</i><span>Dashboard</span>
                    </a>
                </li>
                <li class="{{ current_class('playground') }}">
                    <a href="{{ route('statamic.cp.playground') }}">
                        <i>@svg('playground')</i><span>Playground</span>
                    </a>
                </li>
            </ul>
            <h6>Content</h6>
            <ul>
                @can('index', 'Statamic\Contracts\Data\Entries\Collection')
                <li class="{{ current_class('collections*') }}">
                    <a href="{{ cp_route('collections.index') }}">
                        <i class="">@svg('content-writing')</i><span>Collections</span>
                    </a>
                </li>
                @endcan
                @can('index', 'Statamic\Contracts\Data\Structures\Structure')
                <li class="{{ current_class('structures*') }}">
                    <a href="{{ cp_route('structures.index') }}" class="active">
                        <i>@svg('hierarchy-files')</i><span>Structure</span>
                    </a>
                </li>
                @endcan
                <li class="{{ current_class('taxonomies*') }}">
                    <a href="">
                        <i>@svg('tags')</i><span>Taxonomies</span>
                    </a>
                </li>
                <li class="{{ current_class('assets*') }}">
                    <a href="{{ cp_route('assets.index') }}">
                        <i>@svg('assets')</i><span>Assets</span>
                    </a>
                </li>
                <li class="{{ current_class('globals*') }}">
                    <a href="{{ cp_route('globals.index') }}">
                        <i>@svg('earth')</i><span>Globals</span>
                    </a>
                </li>
            </ul>
            <h6>Tools</h6>
            <ul>
                <li class="{{ current_class('forms*') }}">
                    <a href="{{ cp_route('forms.index') }}">
                        <i>@svg('drawer-file')</i><span>Forms</span>
                    </a>
                </li>
                <li class="{{ current_class('updater*') }}">
                    <a href="{{ route('statamic.cp.updater.index') }}">
                        <i>@svg('loading-bar')</i><span>Updates</span>
                        <updates-badge class="ml-1"></updates-badge>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i>@svg('seo-search-graph')</i><span>SEO Pro</span>
                    </a>
                </li>
            </ul>
            <h6>Users</h6>
            <ul>
                <li class="{{ current_class('users*') }}">
                    <a href="{{ cp_route('users.index') }}">
                        <i>@svg('users-box')</i><span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i>@svg('users-multiple')</i><span>Groups</span>
                    </a>
                </li>
                <li class="{{ current_class('roles*') }}">
                    <a href="{{ cp_route('roles.index') }}">
                        <i>@svg('shield-key')</i><span>Permissions</span>
                    </a>
                </li>
            </ul>
            <h6>Site</h6>
            <ul>
                <li class="{{ current_class('addons') }}">
                    <a href="{{ route('statamic.cp.addons.index') }}">
                        <i>@svg('addons')</i><span>Addons</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i>@svg('hammer-wench')</i><span>Preferences</span>
                    </a>
                </li>
                <li class="{{ current_class('fieldsets*') }}">
                    <a href="{{ cp_route('fieldsets.index') }}">
                        <i>@svg('wireframe')</i><span>Fieldsets</span>
                    </a>
                </li>
                <li class="{{ current_class('blueprints*') }}">
                    <a href="{{ cp_route('blueprints.index') }}">
                        <i>@svg('blueprints')</i><span>Blueprints</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
@stop

@yield('nav-main')
