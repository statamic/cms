@section('nav-main')
    <nav class="new-nav-main">
        <div class="nav-main-wrapper">
            <ul class="mt-sm">
                <li class="{{ current_class('dashboard') }}">
                    <a href="{{ route('statamic.cp.dashboard') }}">
                        <i>@svg('new/pie-line-graph-desktop')</i><span>Dashboard</span>
                    </a>
                </li>
                <li class="{{ current_class('playground') }}">
                    <a href="{{ route('statamic.cp.playground') }}">
                        <i>@svg('new/family-outdoors-playhouse-swing')</i><span>Playground</span>
                    </a>
                </li>
            </ul>
            <h6>Content</h6>
            <ul>
                @can('index', 'Statamic\Contracts\Data\Entries\Collection')
                <li>
                    <a href="{{ cp_route('collections.index') }}">
                        <i class="">@svg('new/content-pencil-write')</i><span>Collections</span>
                    </a>
                </li>
                @endcan
                @can('index', 'Statamic\Contracts\Data\Structures\Structure')
                <li>
                    <a href="{{ cp_route('structures.index') }}" class="active">
                        <i>@svg('new/hierarchy-files-1')</i><span>Structure</span>
                    </a>
                </li>
                @endcan
                <li>
                    <a href="">
                        <i>@svg('new/tags-1')</i><span>Tags</span>
                    </a>
                </li>
                <li>
                    <a href="{{ cp_route('assets.index') }}">
                        <i>@svg('new/picture-polaroid-landscape')</i><span>Assets</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i>@svg('new/earth-1')</i><span>Globals</span>
                    </a>
                </li>
            </ul>
            <h6>Tools</h6>
            <ul>
                <li>
                    <a href="">
                        <i>@svg('new/drawer-file')</i><span>Forms</span>
                    </a>
                </li>
                <li class="{{ current_class('updater') }}">
                    <a href="{{ route('statamic.cp.updater.index') }}">
                        <i>@svg('new/loading-bar-1')</i><span>Updates</span>
                        <updates-badge class="ml-1"></updates-badge>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i>@svg('new/seo-search-graph')</i><span>SEO Pro</span>
                    </a>
                </li>
            </ul>
            <h6>Users</h6>
            <ul>
                <li>
                    <a href="">
                        <i>@svg('new/single-neutral-folder-box')</i><span>Users</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i>@svg('new/multiple-users-2')</i><span>Groups</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i>@svg('new/shield-key')</i><span>Permissions</span>
                    </a>
                </li>
            </ul>
            <h6>Site</h6>
            <ul>
                <li class="{{ current_class('addons') }}">
                    <a href="{{ route('statamic.cp.addons.index') }}">
                        <i>@svg('new/module-three-2')</i><span>Addons</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i>@svg('new/hammer-wench')</i><span>Preferences</span>
                    </a>
                </li>
                <li>
                    <a href="{{ cp_route('fieldsets.index') }}">
                        <i>@svg('new/hammer-wench')</i><span>Fieldsets</span>
                    </a>
                </li>
                <li>
                    <a href="{{ cp_route('blueprints.index') }}">
                        <i>@svg('new/hammer-wench')</i><span>Blueprints</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
@stop

@yield('nav-main')
