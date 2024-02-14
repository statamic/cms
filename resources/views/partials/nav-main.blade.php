@php use function Statamic\trans as __; @endphp

@section('nav-main')
    <nav class="nav-main" v-cloak>
        <div class="nav-main-inner">
            @foreach ($nav as $section)
                @if ($section['display'] !== 'Top Level')
                    <h6 v-pre>{{ Statamic\trans($section['display']) }}</h6>
                @endif
                <ul class="nav-section-{{ Statamic\Support\Str::slug($section['display']) }}">
                    @foreach ($section['items'] as $item)
                        @unless ($item->view())
                            <li class="{{ $item->isActive() ? 'current' : '' }}" v-pre>
                                <a href="{{ $item->url() }}" {{ $item->attributes() }}>
                                    <i>{!! $item->icon() !!}</i><span>{{ Statamic\trans($item->name()) }}</span>
                                </a>
                                @if ($item->children() && $item->isActive())
                                    <ul>
                                        @foreach ($item->children() as $child)
                                            <li class="{{ $child->isActive() ? 'current' : '' }}">
                                                <a href="{{ $child->url() }}" {{ $item->attributes() }}>{{ Statamic\trans($child->name()) }}</a>
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
