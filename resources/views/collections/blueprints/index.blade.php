@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')
    <collection-blueprint-listing
        :initial-rows="{{ json_encode($blueprints) }}"
        reorder-url="{{ cp_route('blueprints.collections.reorder', $collection) }}"
        create-url="{{ cp_route('blueprints.collections.create', $collection) }}"
    ></collection-blueprint-listing>

    <x-statamic::docs-callout
        topic="{{ __('Blueprints') }}"
        url="{{ Statamic::docsUrl('blueprints') }}"
    />
@endsection
