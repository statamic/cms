<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->svg() !!}</i>
        <span>{{ __($item->name()) }}</span>
        <span class="badge-sm bg-red-500 dark:bg-blue-900 ltr:ml-2 rtl:mr-2">
            {{ Statamic\Facades\Stache::duplicates()->count() }}
        </span>
    </a>
</li>
