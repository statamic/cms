@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')
    <blueprint-builder
        show-title
        action="{{ cp_route('blueprints.update', [$blueprint->namespace(), $blueprint->handle()]) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

    <x-statamic::docs-callout :topic="__('Blueprints')" :url="Statamic::docsUrl('blueprints')" />
@endsection
