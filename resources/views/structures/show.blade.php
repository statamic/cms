@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Statamic::crumb($structure->title(), 'Navigation'))

@section('content')

    <structure-view
        title="{{ $structure->title() }}"
        breadcrumb-url="{{ cp_route('structures.index') }}"
        pages-url="{{ cp_route('structures.pages.index', $structure->handle()) }}"
        submit-url="{{ cp_route('structures.pages.store', $structure->handle()) }}"
        edit-url="{{ cp_route('structures.edit', $structure->handle()) }}"
        site="{{ $site }}"
        :collections="{{ json_encode($collections) }}"
        :max-depth="{{ $structure->maxDepth() ?? 'Infinity' }}"
        :expects-root="{{ $str::bool($expectsRoot) }}"
    ></structure-view>

@endsection
