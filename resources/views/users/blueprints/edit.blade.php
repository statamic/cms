@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('users.index'),
        'title' => __('Users'),
    ])

    <blueprint-builder
        action="{{ cp_route('users.blueprint.update') }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
