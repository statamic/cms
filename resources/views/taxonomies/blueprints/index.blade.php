@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title',)

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('taxonomies.show', $taxonomy->handle()),
        'title' => $taxonomy->title()
    ])

    <taxonomy-blueprint-listing
        :initial-rows="{{ json_encode($blueprints) }}"
        reorder-url="{{ cp_route('taxonomies.blueprints.reorder', $taxonomy) }}"
        create-url="{{ cp_route('taxonomies.blueprints.create', $taxonomy) }}"
    ></taxonomy-blueprint-listing>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
