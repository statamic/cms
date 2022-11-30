@php use function Statamic\trans as __; @endphp

@section('nav-main')
    <nav class="nav-main" v-cloak>
        <div class="nav-main-inner">

            @foreach ($nav as $section => $items)
                @if ($section !== 'Top Level')
                    <h6>{{ __($section) }}</h6>
                @endif
                <ul class="nav-section-{{ Statamic\Support\Str::slug($section) }}">
                    @foreach ($items as $item)
                        @unless ($item->view())
                            <li class="{{ $item->isActive() ? 'current' : '' }}">
                                <a href="{{ $item->url() }}" {{ $item->attributes() }}>
                                    <i>{!! $item->icon() !!}</i><span>{{ __($item->name()) }}</span>
                                </a>
                                @if ($item->children() && $item->isActive())
                                    <ul>
                                        @foreach ($item->children() as $child)
                                            <li class="{{ $child->isActive() ? 'current' : '' }}">
                                                <a href="{{ $child->url() }}" {{ $item->attributes() }}>{{ __($child->name()) }}</a>
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

            <div class="opacity-50">
                <h6>{{ __('Preferences') }}</h6>
                <ul>
                    <li>
                        <a href="{{ cp_route('nav-preferences') }}">
                            <i>@cp_svg('hammer-wrench')</i>
                            <span>{{ __('Nav') }}</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>
@stop

@yield('nav-main')
