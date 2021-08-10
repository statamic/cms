@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('navigation.show', $nav->handle()),
        'title' => $nav->title(),
    ])

    <blueprint-builder
        action="{{ cp_route('navigation.blueprint.update', $nav->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :use-sections="false"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
