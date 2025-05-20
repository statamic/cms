@extends("statamic::layout")
@section("title", Statamic::crumb($collection->title(), "Collections"))
@section("wrapper_class", "max-w-full")

@section("content")
    <collection-view
        title="{{ $collection->title() }}"
        handle="{{ $collection->handle() }}"
        icon="{{ $collection->icon() }}"
        breadcrumb-url="{{ cp_route("collections.index") }}"
        :can-create="{{ Statamic\Support\Str::bool($canCreate) }}"
        :create-urls="{{ Js::from($createUrls) }}"
        create-label="{{ $collection->createLabel() }}"
        :blueprints="{{ Js::from($blueprints) }}"
        sort-column="{{ $collection->sortField() }}"
        sort-direction="{{ $collection->sortDirection() }}"
        :columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route("collections.entries.actions.run", $collection->handle()) }}"
        reorder-url="{{ cp_route("collections.entries.reorder", $collection->handle()) }}"
        initial-site="{{ $site }}"
        :sites="{{ json_encode($sites) }}"
        :can-change-localization-delete-behavior="{{ Statamic\Support\Str::bool($canChangeLocalizationDeleteBehavior) }}"
        @if ($collection->hasStructure())
            :structured="{{ Statamic\Support\Str::bool($user->can("reorder", $collection)) }}"
        structure-pages-url="{{ cp_route("collections.tree.index", $structure->handle()) }}"
        structure-submit-url="{{ cp_route("collections.tree.update", $collection->handle()) }}"
        :structure-max-depth="{{ $structure->maxDepth() ?? "Infinity" }}"
        :structure-expects-root="{{ Statamic\Support\Str::bool($structure->expectsRoot()) }}"
        :structure-show-slugs="{{ Statamic\Support\Str::bool($structure->showSlugs()) }}"
        @endif
    >
        @if (

            auth()
                ->user()
                ->can("edit", $collection) ||
            auth()
                ->user()
                ->can("delete", $collection) ||
            auth()
                ->user()
                ->can("configure fields") ||
            $actions->isNotEmpty()        )
            <template #twirldown="{ actionCompleted }">
                @can("edit", $collection)
                    <dropdown-item
                        :text="__('Edit Collection')"
                        redirect="{{ $collection->editUrl() }}"
                    ></dropdown-item>
                @endcan

                @can("configure fields")
                    <dropdown-item
                        :text="__('Edit Blueprints')"
                        redirect="{{ cp_route("collections.blueprints.index", $collection) }}"
                    ></dropdown-item>
                @endcan

                @can("edit", $collection)
                    <dropdown-item
                        :text="__('Scaffold Views')"
                        redirect="{{ cp_route("collections.scaffold", $collection->handle()) }}"
                    ></dropdown-item>
                @endcan

                <data-list-inline-actions
                    item="{{ $collection->handle() }}"
                    url="{{ cp_route("collections.actions.run", ["collection" => $collection->handle()]) }}"
                    :actions="{{ $actions }}"
                    @completed="actionCompleted"
                ></data-list-inline-actions>
            </template>
        @endif
    </collection-view>
@endsection
