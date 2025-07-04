@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Navigation'))

@section('content')
    <ui-header title="{{  __('Navigation') }}" icon="navigation">
        @can('create', 'Statamic\Contracts\Structures\Nav')
            <ui-button
                href="{{ cp_route('navigation.create') }}"
                text="{{ __('Create Navigation') }}"
                variant="primary"
            />
        @endcan
    </ui-header>

    <navigation-listing
        :navigations="{{ json_encode($navs) }}"
    ></navigation-listing>

    <x-statamic::docs-callout
        topic="{{ __('Navigation') }}"
        url="{{ Statamic::docsUrl('navigation') }}"
    />
@endsection
