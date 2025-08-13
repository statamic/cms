@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Taxonomies'))

@section('content')
    <ui-header title="{{ __('Taxonomies') }}" icon="taxonomies">
        @can('create', 'Statamic\Contracts\Taxonomies\Taxonomy')
            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                prioritize
                text="{{ __('Create Taxonomy') }}"
                url="{{ cp_route('taxonomies.create') }}"
                icon="taxonomies"
                v-slot="{ text, url }"
            >
                <ui-button
                    :text="text"
                    :href="url"
                    variant="primary"
                ></ui-button>
            </ui-command-palette-item>
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
