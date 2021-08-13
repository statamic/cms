@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Statamic::crumb($nav->title(), 'Navigation'))

@section('content')

    <navigation-view
        title="{{ $nav->title() }}"
        handle="{{ $nav->handle() }}"
        breadcrumb-url="{{ cp_route('navigation.index') }}"
        pages-url="{{ cp_route('navigation.tree.index', $nav->handle()) }}"
        submit-url="{{ cp_route('navigation.tree.update', $nav->handle()) }}"
        edit-url="{{ $nav->editUrl() }}"
        site="{{ $site }}"
        :sites="{{ json_encode($sites) }}"
        :collections="{{ json_encode($collections) }}"
        :max-depth="{{ $nav->maxDepth() ?? 'Infinity' }}"
        :expects-root="{{ $str::bool($expectsRoot) }}"
        :blueprint="{{ json_encode($blueprint) }}"
    >
        <template #twirldown>
            @can('edit', $nav)
                <dropdown-item :text="__('Edit Navigation')" redirect="{{ $nav->editUrl() }}"></dropdown-item>
            @endcan
            @can('configure fields')
                <dropdown-item :text="__('Edit Blueprint')" redirect="{{ cp_route('navigation.blueprint.edit', $nav->handle()) }}"></dropdown-item>
            @endcan
        </template>
    </navigation-view>

@endsection
