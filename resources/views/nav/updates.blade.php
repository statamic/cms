<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->icon() !!}</i><span v-pre>{{ __($item->name()) }}</span>
        <updates-badge class="rtl:mr-2 ltr:ml-2"></updates-badge>
    </a>
</li>
