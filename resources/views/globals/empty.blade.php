@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Global Sets'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
            <ui-icon name="globals" class="size-5 text-gray-500"></ui-icon>
            {{ __('Globals') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.global_set_config_intro') }}">
        <ui-empty-state-item
            href="{{ cp_route('globals.create') }}"
            icon="globals"
            heading="{{ __('Create Global Set') }}"
            description="{{ __('statamic::messages.global_next_steps_create_description') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        :topic="__('Global Variables')"
        :url="Statamic::docsUrl('globals')"
    />
@endsection
