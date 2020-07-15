@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    <blueprint-builder
        action="{{ cp_route('asset-containers.blueprint.update', $container->handle()) }}"
        breadcrumb-url="{{ cp_route('assets.browse.index', $container->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
