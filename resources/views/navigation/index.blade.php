@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Navigation'))

@section('content')
    <ui-header title="{{  __('Navigation') }}" icon="navigation">
        @can('create', 'Statamic\Contracts\Structures\Nav')
            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                prioritize
                text="{{ __('Create Navigation') }}"
                url="{{ cp_route('navigation.create') }}"
                icon="navigation"
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

    <navigation-listing
        :navigations="{{ json_encode($navs) }}"
    ></navigation-listing>

    <x-statamic::docs-callout
        topic="{{ __('Navigation') }}"
        url="{{ Statamic::docsUrl('navigation') }}"
    />
@endsection
