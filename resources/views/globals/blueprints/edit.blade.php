@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')
    <blueprint-builder
        action="{{ cp_route('globals.blueprint.update', $set->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

    @include(
        'statamic::partials.docs-callout',
        [
            'topic' => __('Blueprints'),
            'url' => Statamic::docsUrl('blueprints'),
        ]
    )
@endsection
