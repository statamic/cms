<li>
    <a href="{{ $item->url() }}" class="flex items-center gap-3 {{ $item->isActive() ? 'active' : '' }}">
        <i>{!! $item->svg() !!}</i>
        <span>{{ __($item->name()) }}</span>
        <span class="badge-sm bg-red-500 dark:bg-blue-900">
            {{ Statamic\Facades\Stache::duplicates()->count() }}
        </span>
    </a>
</li>
