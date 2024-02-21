<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->icon() !!}</i><span v-pre>{{ __($item->name()) }}</span>
        <updates-badge class="ml-2"></updates-badge>
    </a>
</li>
