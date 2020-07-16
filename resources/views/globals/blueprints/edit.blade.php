@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('globals.variables.edit', $set->handle()),
        'title' => $set->title(),
    ])

    <blueprint-builder
        action="{{ cp_route('globals.blueprint.update', $set->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
