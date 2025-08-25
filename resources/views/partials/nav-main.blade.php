@php
    use function Statamic\trans as __;
@endphp

@section("nav-main")
<nav class="nav-main">
    @foreach ($nav as $section)
        <div>
            @if ($section["display"] !== "Top Level")
                <div class="section-title">{{ __($section["display"]) }}</div>
            @endif

            <ul>
                @foreach ($section["items"] as $item)
                    @unless ($item->view())
                        <li
                            v-pre
                            class="group relative"
                            @if (! $item->isActive())
                                x-data="{ open: false, shift: false, mouse: false }"
                                x-on:keydown.shift.window="shift = true"
                                x-on:keyup.shift.window="shift = false"
                                x-on:mouseenter="mouse = true"
                                x-on:mouseleave="mouse = false"
                                x-effect="if (shift) open = mouse"
                            @endif
                        >
                            <a
                                @class([
                                    "active" => $item->isActive(),
                                    "!pr-7 peer" => $item->children() && ! $item->isActive(),
                                ])
                                href="{{ $item->url() }}"
                                {{ $item->attributes() }}
                            >
                                {!! $item->svg() !!}
                                <span class="flex-grow">
                                    {{ __($item->name()) }}
                                </span>
                            </a>
                            @if ($item->children() && ! $item->isActive())
                                <button
                                    class="cursor-pointer opacity-0 group-hover:opacity-100 peer-focus:opacity-100 focus:opacity-100 transition h-7 aspect-square absolute top-0 right-0 z-10"
                                    x-on:click.stop.prevent="open = !open"
                                    :aria-expanded="open ? 'true' : 'false'"
                                    aria-controls="{{ $submenu_id = Str::random(4) }}-submenu"
                                    aria-label="Toggle Submenu"
                                >
                                    <div
                                        class="transform flex items-center w-full h-full justify-center"
                                        x-bind:class="{ 'rotate-180': open }"
                                    >
                                        @cp_svg("ui/chevron-down", "h-5")
                                    </div>
                                </button>
                            @endif

                            @if ($item->children())
                                <ul
                                    @if (! $item->isActive())
                                        id="{{ $submenu_id }}-submenu"
                                        x-show="open"
                                        x-on:click.outside="open = false"
                                        style="display: none;"
                                    @endif
                                >
                                    @foreach ($item->children() as $child)
                                        <li>
                                            <a
                                                href="{{ $child->url() }}"
                                                {{ $item->attributes() }}
                                                @class(["active" => $child->isActive()])
                                            >
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

@yield("nav-main")
