@foreach ($items as $item)

    <li class="nav-{{ $item->name() }} {{ nav_is($item->url()) ? 'visible active' : '' }}">
        <a href="{{ $item->url() }}">

            @if ($item->icon())
                <span class="icon icon-{{ $item->icon() }}"></span>
            @endif

            <span class="title">{{ $item->title() }}</span>

            @if ($item->badge())
                <span class="badge bg-red">{{ $item->badge() }}</span>
            @endif

        </a>

        @if (! $item->children()->isEmpty())
            <ul>
                @include('partials.nav-main-items', ['items' => $item->children()])
            </ul>
        @endif
    </li>

@endforeach
