@php
    use function Statamic\trans as __;
@endphp

@section('nav-main')
<nav class="absolute h-screen w-56 select-none overflow-scroll px-5 text-sm antialiased  [&_svg]:text-gray-400 dark:[&_svg]:text-gray-600 py-6 flex flex-col gap-7">
    @foreach ($nav as $section)
        <div>
            @if ($section['display'] !== 'Top Level')
                <ui-heading size="sm" class="mb-2 text-black dark:text-gray-200">
                    {{ __($section['display']) }}
                </ui-heading>
            @endif
            <ul class="flex flex-col gap-2">
                @foreach ($section['items'] as $item)
                    @unless ($item->view())
                        <li class="{{ $item->isActive() ? '[&_a]:text-black [&_svg]:text-black!' : '' }}" v-pre>
                            <a
                                class="flex items-center gap-3 text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-gray-200 hover:[&_svg]:text-gray-700 dark:hover:[&_svg]:text-gray-200"
                                href="{{ $item->url() }}"
                                {{ $item->attributes() }}
                            >
                                {!! $item->svg() !!}
                                <span>{{ __($item->name()) }}</span>
                            </a>
                            @if ($item->children() && $item->isActive())
                                <ul class="pl-7 py-1 space-y-1">
                                    @foreach ($item->children() as $child)
                                        <li class="{{ $child->isActive() ? '[&_a]:text-black [&_svg]:text-black' : '' }}">
                                            <a href="{{ $child->url() }}" {{ $item->attributes() }}>
                                                {{ __($child->name()) }}
                                            </a>
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
        </div>
        @endforeach
</nav>
@stop

@yield('nav-main')
