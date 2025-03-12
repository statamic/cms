@php
    use function Statamic\trans as __;
@endphp

@php
    use Statamic\Support\Arr;
@endphp

<div class="card overflow-hidden p-0">
    <div class="flex items-center justify-between border-b p-4 dark:border-b dark:border-dark-900 dark:bg-dark-650">
        <h2>
            <a class="flex items-center" href="{{ $form->showUrl() }}">
                <div class="h-6 w-6 text-gray-800 dark:text-dark-200 ltr:mr-2 rtl:ml-2">
                    @cp_svg('icons/light/drawer-file')
                </div>
                <span v-pre>{{ $title }}</span>
            </a>
        </h2>
    </div>
    <form-widget
        form="{{ $form->handle() }}"
        :additional-columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        initial-sort-column="{{ $sortColumn }}"
        initial-sort-direction="{{ $sortDirection }}"
        :initial-per-page="{{ $limit }}"
    ></form-widget>
</div>
