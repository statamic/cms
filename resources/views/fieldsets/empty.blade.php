@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Fieldsets'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="mt-8 py-8 text-center">
        <h1 class="flex items-center justify-center gap-2 text-[25px] font-medium antialiased">
            <ui-icon name="fieldsets" class="size-5 text-gray-500"></ui-icon>
            {{ __('Fieldsets') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.fieldset_intro') }}">
        <ui-empty-state-item
            href="{{ cp_route('fieldsets.create') }}"
            icon="fieldsets"
            heading="{{ __('Create Fieldset') }}"
            description="{{ __('Get started by creating your first fieldset.') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout :topic="__('Blueprints')" :url="Statamic::docsUrl('blueprints')" />
@endsection
