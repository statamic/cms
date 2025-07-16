@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Roles & Permissions'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
            <ui-icon name="permissions" class="size-5 text-gray-500"></ui-icon>
            {{ __('Roles & Permissions') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.role_intro') }}">
        <ui-empty-state-item
            href="{{ cp_route('roles.create') }}"
            icon="permissions"
            heading="{{ __('Create Role') }}"
            description="{{ __('Get started by creating your first role.') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        :topic="__('Roles & Permissions')"
        :url="Statamic::docsUrl('users#permissions')"
    />
@endsection
