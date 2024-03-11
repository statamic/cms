@php use Statamic\Facades\Site; @endphp

<div class="card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-4">
        <h2>
            <a class="flex items-center" href="{{ $collection->showUrl() }}">
                <div class="h-6 w-6 rtl:ml-2 ltr:mr-2 text-gray-800">
                    @cp_svg('icons/light/content-writing')
                </div>
                <span v-pre>{{ __($title) }}</span>
            </a>
        </h2>
        @can('create', ['Statamic\Contracts\Entries\Entry', $collection])
        <create-entry-button
            button-class="btn-primary"
            url="{{ $collection->createEntryUrl(Site::selected()) }}"
            :blueprints="{{ $blueprints->toJson() }}"
            text="{{ $button }}"></create-entry-button>
        @endcan
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
