@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    <blueprint-builder
        action="{{ cp_route('users.blueprint.update') }}"
        breadcrumb-url="{{ cp_route('users.index') }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
