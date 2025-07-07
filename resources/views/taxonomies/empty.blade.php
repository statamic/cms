@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Taxonomies'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
            <ui-icon name="taxonomies" class="size-5 text-gray-500"></ui-icon>
            {{ __('Taxonomies') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.taxonomy_configure_intro') }}">
        <ui-empty-state-item
            href="{{ cp_route('taxonomies.create') }}"
            icon="taxonomies"
            heading="{{ __('Create Taxonomy') }}"
            description="{{ __('Get started by creating your first taxonomy.') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        :topic="__('Taxonomies')"
        :url="Statamic::docsUrl('taxonomies')"
    />
@endsection
