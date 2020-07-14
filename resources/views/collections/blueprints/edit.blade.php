@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    <blueprint-builder
        action="{{ cp_route('collections.blueprints.update', [$collection, $blueprint]) }}"
        breadcrumb-url="{{ cp_route('collections.blueprints.index', $collection) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
