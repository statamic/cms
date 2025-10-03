@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')
    <collection-blueprint-listing
        :initial-rows="{{ json_encode($blueprints) }}"
        reorder-url="{{ cp_route('blueprints.taxonomies.reorder', $taxonomy) }}"
        create-url="{{ cp_route('blueprints.taxonomies.create', $taxonomy) }}"
    ></collection-blueprint-listing>

    <x-statamic::docs-callout
        topic="{{ __('Blueprints') }}"
        url="{{ Statamic::docsUrl('blueprints') }}"
    />
@endsection
