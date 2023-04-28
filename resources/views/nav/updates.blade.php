<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->svg() !!}</i><span>{{ __($item->name()) }}</span>
        <updates-badge class="ml-2"></updates-badge>
    </a>
</li>
