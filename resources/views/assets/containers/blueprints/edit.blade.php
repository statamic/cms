@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')
    <blueprint-builder
        action="{{ cp_route('asset-containers.blueprint.update', $container->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :use-tabs="false"
        :can-define-localizable="false"
    ></blueprint-builder>

    <x-statamic::docs-callout :topic="__('Blueprints')" :url="Statamic::docsUrl('blueprints')" />
@endsection
