@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('taxonomies.blueprints.index', $taxonomy),
        'title' => __('Blueprints')
    ])

    <blueprint-builder
        action="{{ cp_route('taxonomies.blueprints.update', [$taxonomy, $blueprint]) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
