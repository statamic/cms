@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Statamic::crumb($nav->title(), 'Navigation'))

@section('content')
    <navigation-view
        title="{{ $nav->title() }}"
        handle="{{ $nav->handle() }}"
        pages-url="{{ cp_route('navigation.tree.index', $nav->handle()) }}"
        submit-url="{{ cp_route('navigation.tree.update', $nav->handle()) }}"
        edit-url="{{ $nav->editUrl() }}"
        blueprint-url="{{ cp_route('navigation.blueprint.edit', $nav->handle()) }}"
        site="{{ $site }}"
        :sites="{{ json_encode($sites) }}"
        :collections="{{ json_encode($collections) }}"
        :max-depth="{{ $nav->maxDepth() ?? 'Infinity' }}"
        :expects-root="{{ $str::bool($expectsRoot) }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :can-edit="{{ Statamic\Support\Str::bool($user->can('edit', $nav)) }}"
        :can-select-across-sites="{{ Statamic\Support\Str::bool($nav->canSelectAcrossSites()) }}"
        :can-edit-blueprint="{{ Statamic\Support\Str::bool($user->can('configure fields')) }}"
    ></navigation-view>
@endsection
