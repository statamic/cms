@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')
    <taxonomy-blueprint-listing
        :initial-rows="{{ json_encode($blueprints) }}"
        reorder-url="{{ cp_route('taxonomies.blueprints.reorder', $taxonomy) }}"
        create-url="{{ cp_route('taxonomies.blueprints.create', $taxonomy) }}"
    ></taxonomy-blueprint-listing>

    <x-statamic::docs-callout
        topic="{{ __('Blueprints') }}"
        url="{{ Statamic::docsUrl('blueprints') }}"
    />
@endsection
