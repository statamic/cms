<div class="card p-0 overflow-hidden">
    <div class="flex justify-between items-center p-2">
        <h2>{{ $title }}</h2>
        <a href="{{ $collection->createEntryUrl() }}" class="text-blue hover:text-blue-dark text-sm">{{ $button }}</a>
    </div>
    <collection-widget
        collection="{{ $collection->handle() }}"
        initial-sort-column="{{ $sortColumn }}"
        initial-sort-direction="{{ $sortDirection }}"
        :initial-per-page="{{ $limit }}"
    ></collection-widget>
</div>
