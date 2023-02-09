<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->icon() !!}</i><span>{{ __($item->name()) }}</span>
        <updates-badge class="ml-2___REPLACED"></updates-badge>
    </a>
</li>
