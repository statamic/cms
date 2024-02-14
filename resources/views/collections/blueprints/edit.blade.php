@extends('statamic::layout')
@section('title', Statamic\trans('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('collections.blueprints.index', $collection),
        'title' => Statamic\trans('Blueprints')
    ])

    <blueprint-builder
        show-title
        action="{{ cp_route('collections.blueprints.update', [$collection, $blueprint]) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
