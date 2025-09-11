@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Global Sets'))

@section('content')
    <ui-header title="{{ __('Globals') }}" icon="globals">
        @can('create', 'Statamic\Contracts\Globals\GlobalSet')
            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                prioritize
                text="{{ __('Create Global Set') }}"
                url="{{ cp_route('globals.create') }}"
                icon="globals"
                v-slot="{ text, url }"
            >
                <ui-button
                    :text="text"
                    :href="url"
                    variant="primary"
                />
            </ui-command-palette-item>
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
