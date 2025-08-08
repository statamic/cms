@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Addons'))

@section('content')
    <ui-header title="{{ __('Addons') }}" icon="addons">
        <ui-button variant="primary" text="Browse the Marketplace" icon="external-link"></ui-button>
    </ui-header>

    <addon-list :initial-rows="{{ json_encode($addons) }}" :initial-columns="{{ json_encode($columns) }}"></addon-list>

    <x-statamic::docs-callout
        :topic="__('Addons')"
        :url="Statamic::docsUrl('addons')"
    />
@endsection
