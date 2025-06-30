@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($taxonomy->title(), 'Taxonomies'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2">
            <ui-icon name="taxonomies" class="size-5 text-gray-500"></ui-icon>
            {{ $taxonomy->title() }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('Start designing your taxonomy with these steps') }}">
        @can('edit', $taxonomy)
            <ui-empty-state-item
                href="{{ cp_route('taxonomies.edit', $taxonomy->handle()) }}"
                icon="configure-large"
                heading="{{ __('Configure Collection') }}"
                description="{{ __('statamic::messages.taxonomy_next_steps_configure_description') }}"
            ></ui-empty-state-item>
        @endcan

        @can('create', ['Statamic\Contracts\Taxonomies\Term', $taxonomy, \Statamic\Facades\Site::get($site)])
            <ui-empty-state-item
                href="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}"
                icon="fieldtype-taxonomy"
                heading="{{ __('Create Term') }}"
                description="{{ __('statamic::messages.taxonomy_next_steps_create_term_description') }}"
            ></ui-empty-state-item>
        @endcan

        @can('configure fields')
            <ui-empty-state-item
                href="{{ cp_route('taxonomies.blueprints.index', [$taxonomy->handle()]) }}"
                icon="blueprints-large"
                heading="{{ __('Configure Blueprints') }}"
                description="{{ __('statamic::messages.taxonomy_next_steps_blueprints_description') }}"
            ></ui-empty-state-item>
        @endcan
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        :topic="__('Taxonomies')"
        :url="Statamic::docsUrl('taxonomies')"
    />
@stop
