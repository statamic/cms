@section('nav-main')
    <nav class="nav-main" v-cloak>
        <div class="nav-main-wrapper">
            @foreach (Statamic\API\Nav::build() as $section => $items)
                @if ($section !== 'Top Level')
                    <h6>{{ __($section) }}</h6>
                @endif
                <ul>
                    @foreach ($items as $item)
                        @unless ($item->view())
                            <li class="{{ current_class($item->active()) }}">
                                <a href="{{ $item->url() }}">
                                    <i>@svg($item->icon())</i><span>{{ __($item->name()) }}</span>
                                </a>
                                @if ($item->children() && is_current($item->active()))
                                    <ul>
                                        @foreach ($item->children() as $child)
                                            <li class="{{ current_class($child->active()) }}">
                                                <a href="{{ $child->url() }}">{{ __($child->name()) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @else
                            @include($item->view())
                        @endunless
                    @endforeach
                </ul>
            @endforeach
        </div>
    </nav>
@stop

@yield('nav-main')
