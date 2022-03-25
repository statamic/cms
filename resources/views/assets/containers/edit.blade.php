@extends('statamic::layout')
@section('title', __('Configure Asset Container'))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('assets.browse.show', $container->handle()),
            'title' => $container->title()
        ])
        <h1>@yield('title')</h1>
    </header>

    <asset-container-edit-form
        initial-title="{{ $container->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('asset-containers.update', $container->handle()) }}"
        listing-url="{{ cp_route('assets.browse.show', $container->handle()) }}"
        action="patch"
    ></asset-container-edit-form>

@endsection
