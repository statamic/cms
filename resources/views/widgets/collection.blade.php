<div class="card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-2">
        <h2>
            <a class="flex items-center" href="{{ $collection->showUrl() }}">
                <div class="h-6 w-6 mr-1 text-grey-80">
                    @cp_svg('content-writing')
                </div>
                <span>{{ $title }}</span>
            </a>
        </h2>
        @can('create', ['Statamic\Contracts\Entries\Entry', $collection])
        <create-entry-button
            button-class="btn-primary"
            url="{{ $collection->createEntryUrl() }}"
            :blueprints="{{ $blueprints->toJson() }}"
            text="{{ $button }}"></create-entry-button>
        @endcan
    </div>
    <collection-widget
        collection="{{ $collection->handle() }}"
        :filters="{{ $filters->toJson() }}"
        initial-sort-column="{{ $sortColumn }}"
        initial-sort-direction="{{ $sortDirection }}"
        :initial-per-page="{{ $limit }}"
    ></collection-widget>
</div>
