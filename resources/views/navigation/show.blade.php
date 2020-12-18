@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Statamic::crumb($nav->title(), 'Navigation'))

@section('content')

    <navigation-view
        title="{{ $nav->title() }}"
        breadcrumb-url="{{ cp_route('navigation.index') }}"
        pages-url="{{ cp_route('structures.pages.index', $nav->handle()) }}"
        submit-url="{{ cp_route('structures.pages.store', $nav->handle()) }}"
        edit-url="{{ $nav->editUrl() }}"
        site="{{ $site }}"
        :sites="{{ json_encode($sites) }}"
        :collections="{{ json_encode($collections) }}"
        :max-depth="{{ $nav->maxDepth() ?? 'Infinity' }}"
        :expects-root="{{ $str::bool($expectsRoot) }}"
    ></navigation-view>

@endsection
