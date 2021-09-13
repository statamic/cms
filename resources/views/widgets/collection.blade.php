<div class="card p-0 overflow-hidden h-full">
    <div class="flex justify-between items-center p-2">
        <h2 class="flex items-center">
            <div class="h-6 w-6 mr-1 text-grey-80">
                @cp_svg('content-writing')
            </div>
            <span>{{ $title  }}</span>
        </h2>
        <a href="{{ $collection->createEntryUrl() }}" class="text-blue hover:text-blue-dark text-sm">{{ $button }}</a>
    </div>
    <collection-widget
        collection="{{ $collection->handle() }}"
        :filters="{{ $filters->toJson() }}"
        initial-sort-column="{{ $sortColumn }}"
        initial-sort-direction="{{ $sortDirection }}"
        :initial-per-page="{{ $limit }}"
    ></collection-widget>
</div>
