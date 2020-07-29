<nav class="nav-main nav-mobile" v-cloak>
    <div class="nav-main-inner">
        @foreach ($nav as $section => $items)
            @if ($section !== 'Top Level')
                <h6>{{ __($section) }}</h6>
            @endif
            <ul>
                @foreach ($items as $item)
                    @unless ($item->view())
                        <li class="{{ $item->isActive() ? 'current' : '' }}">
                            <a href="{{ $item->url() }}">
                                <i>@svg($item->icon())</i><span>{{ __($item->name()) }}</span>
                            </a>
                            @if ($item->children() && $item->isActive())
                                <ul>
                                    @foreach ($item->children() as $child)
                                        <li class="{{ $child->isActive() ? 'current' : '' }}">
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
