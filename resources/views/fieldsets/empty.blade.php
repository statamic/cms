@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Fieldsets'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <ui-icon name="fieldsets" class="size-5 text-gray-500"></ui-icon>
            {{ __('Fieldsets') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.fieldset_intro') }}">
        <ui-command-palette-item
            category="{{ Statamic\CommandPalette\Category::Actions }}"
            text="{{ __('Create Fieldset') }}"
            icon="fieldsets"
            url="{{ cp_route('fieldsets.create') }}"
            v-slot="{ text, url, icon }"
        >
            <ui-empty-state-item
                :heading="text"
                :href="url"
                :icon="icon"
                description="{{ __('Get started by creating your first fieldset.') }}"
            ></ui-empty-state-item>
        </ui-command-palette-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        :topic="__('Blueprints')"
        :url="Statamic::docsUrl('blueprints')"
    />
@endsection
