@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('collections.blueprints.index', $collection),
        'title' => __('Blueprints')
    ])

    <blueprint-builder
        show-title
        action="{{ cp_route('collections.blueprints.update', [$collection, $blueprint]) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
