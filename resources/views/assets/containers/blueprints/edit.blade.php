@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('assets.browse.index', $container->handle()),
        'title' => $container->title(),
    ])

    <blueprint-builder
        action="{{ cp_route('asset-containers.blueprint.update', $container->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :use-sections="false"
    ></blueprint-builder>

@endsection
