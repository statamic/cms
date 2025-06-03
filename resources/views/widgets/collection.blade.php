@php
    use Statamic\Facades\Site;
    use function Statamic\trans as __;
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
                text="{{ $button }}"
                url="{{ $collection->createEntryUrl(Site::selected()) }}"
                variant="default"
                :blueprints="{{ $blueprints->toJson() }}"
            ></create-entry-button>
        </template>
    @endif
</collection-widget>
