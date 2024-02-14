<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->icon() !!}</i><span v-pre>{{ Statamic\trans($item->name()) }}</span>
        <updates-badge class="ml-2"></updates-badge>
    </a>
</li>
