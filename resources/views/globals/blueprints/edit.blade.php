@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    <blueprint-builder
        action="{{ cp_route('globals.blueprint.update', $set->handle()) }}"
        breadcrumb-url="{{ cp_route('globals.variables.edit', $set->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
