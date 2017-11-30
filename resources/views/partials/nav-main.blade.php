@section('nav-main')
    <ul>
        <li class="nav-dashboard {{ request()->is('cp') ? 'visible active' : '' }}">
            <a href="{{ route('dashboard') }}" title="{{ translate('cp.nav_dashboard') }}">
                <span class="title">{{ translate('cp.nav_dashboard') }}
            </a>
        </li>
        @foreach ($nav->children() as $item)
            <li class="section">{{ $item->title() }}</li>
            @include('partials.nav-main-items', ['items' => $item->children()])
        @endforeach
    </ul>
@stop

@yield('nav-main')
