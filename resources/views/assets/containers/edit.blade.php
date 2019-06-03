@extends('statamic::layout')
@section('title', __('Edit Asset Container'))

@section('content')

    <asset-container-edit-form
        initial-title="{{ $container->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('asset-containers.update', $container->handle()) }}"
        action="patch"
    ></asset-container-edit-form>

@endsection
