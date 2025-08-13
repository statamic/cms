@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Global Sets'))

@section('content')
    <ui-header title="{{ __('Globals') }}" icon="globals">
        @can('create', 'Statamic\Contracts\Globals\GlobalSet')
            <ui-button
                href="{{ cp_route('globals.create') }}"
                text="{{ __('Create Global Set') }}"
                variant="primary"
            />
        @endcan
    </ui-header>

    <global-listing
        :initial-globals="{{ json_encode($globals) }}"
    ></global-listing>

    <x-statamic::docs-callout
        topic="{{ __('Global Variables') }}"
        url="{{ Statamic::docsUrl('globals') }}"
    />
@endsection
