@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    <blueprint-builder
        action="{{ cp_route('blueprints.update', $blueprint->handle()) }}"
        breadcrumb-url="{{ cp_route('blueprints.index') }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
