@php
    use Statamic\Facades\Site;
    use function Statamic\trans as __;
@endphp

<div class="card flex h-full flex-col overflow-hidden p-0">
    <div class="flex items-center justify-between border-b p-4 dark:border-b dark:border-dark-900 dark:bg-dark-650">
        <h2>
            <a class="flex items-center" href="{{ $collection->showUrl() }}">
                <div class="h-6 w-6 text-gray-800 dark:text-dark-200 ltr:mr-2 rtl:ml-2">
                    @cp_svg('icons/light/content-writing')
                </div>
                <span v-pre>{{ __($title) }}</span>
            </a>
        </h2>
        @if ($canCreate)
            <create-entry-button
                button-class="btn-primary"
                url="{{ $collection->createEntryUrl(Site::selected()) }}"
                :blueprints="{{ $blueprints->toJson() }}"
                text="{{ $button }}"
            ></create-entry-button>
        @endif
    </div>
    <collection-widget
        collection="{{ $collection->handle() }}"
        :additional-columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        initial-sort-column="{{ $sortColumn }}"
        initial-sort-direction="{{ $sortDirection }}"
        :initial-per-page="{{ $limit }}"
    ></collection-widget>
</div>
