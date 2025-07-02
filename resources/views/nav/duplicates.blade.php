<li class="{{ $item->isActive() ? 'current' : '' }}">
    <a href="{{ $item->url() }}">
        <i>{!! $item->svg() !!}</i>
        <span>{{ __($item->name()) }}</span>
        <span class="badge-sm bg-red-500 ltr:ml-2 rtl:mr-2 dark:bg-blue-900">
            {{ Statamic\Facades\Stache::duplicates()->count() }}
        </span>
    </a>
</li>
