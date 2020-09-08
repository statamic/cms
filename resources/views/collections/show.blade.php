@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))
@section('wrapper_class', 'max-w-full')

@section('content')

    <collection-view
        title="{{ $collection->title() }}"
        handle="{{ $collection->handle() }}"
        breadcrumb-url="{{ cp_route('collections.index') }}"
        :can-create="@can('create', ['Statamic\Contracts\Entries\Entry', $collection]) true @else false @endcan"
        create-url="{{ cp_route('collections.entries.create', [$collection->handle(), $site]) }}"
        :blueprints='@json($blueprints)'
        sort-column="{{ $collection->sortField() }}"
        sort-direction="{{ $collection->sortDirection() }}"
        :filters="{{ $filters->toJson() }}"
        run-action-url="{{ cp_route('collections.entries.actions.run', $collection->handle()) }}"
        bulk-actions-url="{{ cp_route('collections.entries.actions.bulk', $collection->handle()) }}"
        reorder-url="{{ cp_route('collections.entries.reorder', $collection->handle()) }}"
        initial-site="{{ $site }}"
        :sites="{{ json_encode($sites) }}"

        @if ($collection->hasStructure())
        :structured="{{ Statamic\Support\Str::bool($user->can('reorder', $collection)) }}"
        structure-pages-url="{{ cp_route('structures.pages.index', $structure->handle()) }}"
        structure-submit-url="{{ cp_route('collections.structure.update', $collection->handle()) }}"
        :structure-max-depth="{{ $structure->maxDepth() ?? 'Infinity' }}"
        :structure-expects-root="{{ Statamic\Support\Str::bool($structure->expectsRoot()) }}"
        @endif
    >
        <template #twirldown>
            @can('edit', $collection)
                <dropdown-item :text="__('Edit Collection')" redirect="{{ $collection->editUrl() }}"></dropdown-item>
            @endcan
            @can('configure fields')
                <dropdown-item :text="__('Edit Blueprints')" redirect="{{ cp_route('collections.blueprints.index', $collection) }}"></dropdown-item>
            @endcan
            @can('edit', $collection)
                <dropdown-item :text="__('Scaffold Resources')" redirect="{{ cp_route('collections.scaffold', $collection->handle()) }}"></dropdown-item>
            @endcan
            @can('delete', $collection)
                <dropdown-item :text="__('Delete Collection')" class="warning" @click="$refs.deleter.confirm()">
                    <resource-deleter
                        ref="deleter"
                        resource-title="{{ $collection->title() }}"
                        route="{{ cp_route('collections.destroy', $collection->handle()) }}"
                        redirect="{{ cp_route('collections.index') }}"
                    ></resource-deleter>
                </dropdown-item>
            @endcan
        </template>
    </collection-view>

@endsection
