@php
    use Statamic\Facades\Site;
@endphp

<collection-widget
    collection="{{ $collection->handle() }}"
    title="{{ $title }}"
    :additional-columns="{{ $columns->toJson() }}"
    :filters="{{ $filters->toJson() }}"
    initial-sort-column="{{ $sortColumn }}"
    initial-sort-direction="{{ $sortDirection }}"
    :initial-per-page="{{ $limit }}"
>
    @if ($canCreate)
        <template #actions>
            <create-entry-button
                url="{{ $collection->createEntryUrl(Site::selected()) }}"
                :blueprints="{{ $blueprints->toJson() }}"
                text="{{ $button }}"
            ></create-entry-button>
        </template>
    @endif
</collection-widget>
