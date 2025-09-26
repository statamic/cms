@extends("statamic::layout")
@section("title", Statamic::crumb($collection->title(), "Collections"))

@section("content")
    <collection-view
        :actions="{{ Js::from($actions) }}"
        :blueprints="{{ Js::from($blueprints) }}"
        :can-change-localization-delete-behavior="{{ Statamic\Support\Str::bool($canChangeLocalizationDeleteBehavior) }}"
        :can-create="{{ Statamic\Support\Str::bool($canCreate) }}"
        :can-edit-blueprints="{{ Statamic\Support\Str::bool($user->can("configure fields")) }}"
        :can-edit="{{ Statamic\Support\Str::bool($user->can("edit", $collection)) }}"
        :columns="{{ $columns->toJson() }}"
        :create-urls="{{ Js::from($createUrls) }}"
        :filters="{{ $filters->toJson() }}"
        :sites="{{ json_encode($sites) }}"
        action-url="{{ cp_route("collections.actions.run") }}"
        blueprints-url="{{ cp_route("blueprints.collections.index", $collection) }}"
        create-label="{{ $collection->createLabel() }}"
        edit-url="{{ $collection->editUrl() }}"
        entries-action-url="{{ cp_route("collections.entries.actions.run", $collection->handle()) }}"
        handle="{{ $collection->handle() }}"
        icon="{{ $collection->icon() }}"
        initial-site="{{ $site }}"
        reorder-url="{{ cp_route("collections.entries.reorder", $collection->handle()) }}"
        scaffold-url="{{ cp_route("collections.scaffold", $collection->handle()) }}"
        sort-column="{{ $collection->sortField() }}"
        sort-direction="{{ $collection->sortDirection() }}"
        title="{{ $collection->title() }}"
        @if ($collection->dated())
            :dated="{{ Statamic\Support\Str::bool($collection->dated()) }}"
        @endif
        @if ($collection->hasStructure())
            :structured="{{ Statamic\Support\Str::bool($user->can("reorder", $collection)) }}"
            structure-pages-url="{{ cp_route("collections.tree.index", $structure->handle()) }}"
            structure-submit-url="{{ cp_route("collections.tree.update", $collection->handle()) }}"
            :structure-max-depth="{{ $structure->maxDepth() ?? "Infinity" }}"
            :structure-expects-root="{{ Statamic\Support\Str::bool($structure->expectsRoot()) }}"
            :structure-show-slugs="{{ Statamic\Support\Str::bool($structure->showSlugs()) }}"
        @endif
    ></collection-view>
@endsection
