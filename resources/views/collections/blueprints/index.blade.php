@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('collections.show', $collection->handle()),
        'title' => $collection->title()
    ])

    <collection-blueprint-listing
        :initial-rows="{{ json_encode($blueprints) }}"
        reorder-url="{{ cp_route('collections.blueprints.reorder', $collection) }}"
        create-url="{{ cp_route('collections.blueprints.create', $collection) }}"
    ></collection-blueprint-listing>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
