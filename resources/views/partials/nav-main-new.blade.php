@section('nav-main')
    <nav class="nav-main new antialiased pl-3">
        <ul>
            <li class="current">
                <a href="{{ route('dashboard') }}">
                    <i>@svg('new/pie-line-graph-desktop')</i><span>Dashboard</span>
                </a>
            </li>
        </ul>
        <h6 class="mb-1">Content</h6>
        <ul>
            <li>
                <a href="">
                    <i class="">@svg('new/content-pencil-write')</i><span>Entries</span>
                </a>
            </li>
            <li>
                <a href="" class="active">
                    <i>@svg('new/hierarchy-files-1')</i><span>Structure</span>
                </a>
            </li>
            <li>
                <a href="">
                    <i>@svg('new/tags-1')</i><span>Tags</span>
                </a>
            </li>
            <li>
                <a href="">
                    <i>@svg('new/picture-polaroid-landscape')</i><span>Assets</span>
                </a>
            </li>
            <li>
                <a href="">
                    <i>@svg('new/earth-1')</i><span>Globals</span>
                </a>
            </li>
        </ul>
        <h6 class="mb-1">Tools</h6>
        <ul>
            <li>
                <a href="">
                    <i>@svg('new/drawer-file')</i><span>Forms</span>
                </a>
            </li>
            <li>
                <a href="">
                    <i>@svg('new/loading-bar-1')</i><span>Updates</span>
                </a>
            </li>
            <li>
                <a href="">
                    <i>@svg('new/seo-search-graph')</i><span>SEO Pro</span>
                </a>
            </li>
        </ul>
        <h6 class="mb-1">Users</h6>
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
        <h6 class="mb-1">Site</h6>
        <ul>
            <li>
                <a href="">
                    <i>@svg('new/module-three-2')</i><span>Addons</span>
                </a>
            </li>
            <li>
                <a href="">
                    <i>@svg('new/hammer-wench')</i><span>Preferences</span>
                </a>
            </li>
        </ul>
    </nav>
@stop

@yield('nav-main')
