@section('nav-main')
    <nav class="nav-main" v-cloak>
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

            @foreach (Statamic\API\Nav::build() as $section => $items)
                <h6>{{ __($section) }}</h6>
                <ul>
                    @foreach ($items as $item)
                        @if ($item->view())
                            @include($item->view())
                        @else
                            <li class="{{ current_class($item->currentClass()) }}">
                                <a href="{{ $item->url() }}">
                                    <i>@svg($item->icon())</i><span>{{ __($item->name()) }}</span>
                                </a>
                                @if ($item->children() && is_current($item->currentClass()))
                                    <ul>
                                        @foreach ($item->children() as $child)
                                            <li class="{{ current_class($child->currentClass()) }}">
                                                <a href="{{ $child->url() }}">{{ __($child->name()) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endforeach

        </div>
    </nav>
@stop

@yield('nav-main')
