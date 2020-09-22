@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('assets.browse.show', $container->handle()),
        'title' => $container->title(),
    ])

    <blueprint-builder
        action="{{ cp_route('asset-containers.blueprint.update', $container->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :use-sections="false"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
