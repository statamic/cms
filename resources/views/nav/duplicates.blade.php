<li>
    <a href="{{ $item->url() }}" class="flex items-center gap-2 sm:gap-3 {{ $item->isActive() ? 'active' : '' }}">
        <i>{!! $item->svg() !!}</i>
        <span>{{ __($item->name()) }}</span>
        <ui-badge color="red" size="sm" variant="flat" pill>
            {{ Statamic\Facades\Stache::duplicates()->count() }}
        </ui-badge>
    </a>
</li>
