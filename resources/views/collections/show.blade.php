@extends("statamic::layout")
@section("title", Statamic::crumb($collection->title(), "Collections"))

@section("content")
    <collection-view
        title="{{ $collection->title() }}"
        handle="{{ $collection->handle() }}"
        icon="{{ $collection->icon() }}"
        :can-create="{{ Statamic\Support\Str::bool($canCreate) }}"
        :create-urls="{{ Js::from($createUrls) }}"
        create-label="{{ $collection->createLabel() }}"
        :blueprints="{{ Js::from($blueprints) }}"
        sort-column="{{ $collection->sortField() }}"
        sort-direction="{{ $collection->sortDirection() }}"
        :columns="{{ $columns->toJson() }}"
        :filters="{{ $filters->toJson() }}"
        :actions="{{ Js::from($actions) }}"
        action-url="{{ cp_route("collections.actions.run") }}"
        entries-action-url="{{ cp_route("collections.entries.actions.run", $collection->handle()) }}"
        reorder-url="{{ cp_route("collections.entries.reorder", $collection->handle()) }}"
        edit-url="{{ $collection->editUrl() }}"
        blueprints-url="{{ cp_route("blueprints.collections.index", $collection) }}"
        scaffold-url="{{ cp_route("collections.scaffold", $collection->handle()) }}"
        :can-edit="{{ Statamic\Support\Str::bool($user->can("edit", $collection)) }}"
        :can-edit-blueprints="{{ Statamic\Support\Str::bool($user->can("configure fields")) }}"
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
    ></collection-view>
@endsection
