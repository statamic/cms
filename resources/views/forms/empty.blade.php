@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Forms'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2">
            <ui-icon name="collections" class="size-5 text-gray-500"></ui-icon>
            {{ __('Forms') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.form_configure_intro') }}">
        @if ($user->can('create', 'Statamic\Contracts\Forms\Form'))
            <ui-empty-state-item
                href="{{ cp_route('forms.create') }}"
                icon="forms"
                heading="{{ __('Create Form') }}"
                description="{{ __('statamic::messages.form_create_description') }}"
            ></ui-empty-state-item>
        @endif
        <ui-empty-state-item
            href="{{ cp_route('utilities.email') }}"
            icon="mail-settings"
            heading="{{ __('Configure Email') }}"
            description="{{ __('statamic::messages.form_configure_email_description') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        topic="{{ __('Forms') }}"
        url="{{ Statamic::docsUrl('forms') }}"
    />
@endsection
