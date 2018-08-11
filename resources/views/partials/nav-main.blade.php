@section('nav-main')
    <nav class="nav-main">
        <ul class="mt-0">
            <li class="nav-dashboard {{ request()->is('cp') ? 'visible active' : '' }}">
                <a href="{{ route('dashboard') }}" title="{{ __('Dashboard') }}">
                    <span class="title">{{ __('Dashboard') }}</span>
                </a>
            </li>
            @foreach ($nav->children() as $item)
                <li class="section">{{ $item->title() }}</li>
                @include('statamic::partials.nav-main-items', ['items' => $item->children()])
            @endforeach
            <li class="md:hidden"><a href="{{ route('account') }}">{{ t('profile') }}</a></li>
            <li class="md:hidden"><a href="{{ route('logout') }}">{{ t('sign_out') }}</a></li>
        </ul>
    </nav>
@stop

@yield('nav-main')
