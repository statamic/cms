@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Navigation'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="mt-8 py-8 text-center">
        <h1 class="flex items-center justify-center gap-2 text-[25px] font-medium antialiased">
            <ui-icon name="navigation" class="size-5 text-gray-500"></ui-icon>
            {{ __('Navigation') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.navigation_configure_intro') }}">
        <ui-empty-state-item
            href="{{ cp_route('navigation.create') }}"
            icon="navigation"
            heading="{{ __('Create a Navigation') }}"
            description="{{ __('Get started by creating your first navigation.') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout :topic="__('Navigation')" :url="Statamic::docsUrl('navigation')" />
@endsection
