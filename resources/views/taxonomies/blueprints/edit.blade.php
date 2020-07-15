@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    <blueprint-builder
        action="{{ cp_route('taxonomies.blueprints.update', [$taxonomy, $blueprint]) }}"
        breadcrumb-url="{{ cp_route('taxonomies.blueprints.index', $taxonomy) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
