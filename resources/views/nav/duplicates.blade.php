<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->icon() !!}</i><span>{{ __($item->name()) }}</span>
        <span class="badge-sm bg-red ml-1">{{ Statamic\Facades\Stache::duplicates()->count() }}</span>
    </a>
</li>
