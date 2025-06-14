@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Configure Asset Container'))

@section('content')
    <asset-container-edit-form
        initial-title="{{ __('Configure Asset Container') }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('asset-containers.update', $container->handle()) }}"
        listing-url="{{ cp_route('assets.browse.show', $container->handle()) }}"
        action="patch"
    ></asset-container-edit-form>
@endsection
