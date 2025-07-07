@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Navigation'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
            <ui-icon name="dashboard" class="size-5 text-gray-500"></ui-icon>
            {{ __('Dashboard') }}
        </h1>
    </header>

    <ui-empty-state-menu 
        heading="{{ __('statamic::messages.getting_started_widget_header') }}"
        subheading="{{ __('statamic::messages.getting_started_widget_intro') }}"
    >
        <ui-empty-state-item
            href="https://statamic.dev"
            icon="docs"
            heading="{{ __('Read the Documentation') }}"
            description="{{ __('statamic::messages.getting_started_widget_docs') }}"
        ></ui-empty-state-item>
        @if (! Statamic::pro())
            <ui-empty-state-item
                href="https://statamic.dev/licensing"
                icon="pro-ribbon"
                heading="{{ __('Enable Pro Mode') }}"
                description="{{ __('statamic::messages.getting_started_widget_pro') }}"
            ></ui-empty-state-item>
        @endif
        <ui-empty-state-item
            href="{{ cp_route('blueprints.index') }}"
            icon="blueprints"
            heading="{{ __('Create a Blueprint') }}"
            description="{{ __('statamic::messages.blueprints_intro') }}"
        ></ui-empty-state-item>
        <ui-empty-state-item
            href="{{ cp_route('collections.create') }}"
            icon="collections"
            heading="{{ __('Create a Collection') }}"
            description="{{ __('statamic::messages.getting_started_widget_collections') }}"
        ></ui-empty-state-item>
        <ui-empty-state-item
            href="{{ cp_route('navigation.create') }}"
            icon="navigation"
            heading="{{ __('Create a Navigation') }}"
            description="{{ __('statamic::messages.getting_started_widget_navigation') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        :topic="__('Navigation')"
        :url="Statamic::docsUrl('navigation')"
    />
@endsection
