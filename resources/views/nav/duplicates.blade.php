<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->icon() !!}</i><span>{{ __($item->name()) }}</span>
        <span class="badge-sm bg-red-500 rtl:mr-2 ltr:ml-2">{{ Statamic\Facades\Stache::duplicates()->count() }}</span>
    </a>
</li>
