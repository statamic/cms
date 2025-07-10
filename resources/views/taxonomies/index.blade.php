@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Taxonomies'))

@section('content')
    <ui-header title="{{ __('Taxonomies') }}" icon="taxonomies">

        @can('create', 'Statamic\Contracts\Taxonomies\Taxonomy')
            <ui-button
                href="{{ cp_route('taxonomies.create') }}"
                text="{{ __('Create Taxonomy') }}"
                variant="primary"
            />
        @endcan
    </ui-header>

    <taxonomy-list
        :initial-rows="{{ json_encode($taxonomies) }}"
        :initial-columns="{{ json_encode($columns) }}"
    ></taxonomy-list>

    <x-statamic::docs-callout
        topic="{{ __('Taxonomies') }}"
        url="{{ Statamic::docsUrl('taxonomies') }}"
    />
@endsection
