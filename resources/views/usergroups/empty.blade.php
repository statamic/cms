@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('User Groups'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="mt-8 py-8 text-center">
        <h1 class="flex items-center justify-center gap-2 text-[25px] font-medium antialiased">
            <ui-icon name="groups" class="size-5 text-gray-500"></ui-icon>
            {{ __('User Groups') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.user_groups_intro') }}">
        <ui-empty-state-item
            href="{{ cp_route('user-groups.create') }}"
            icon="groups"
            heading="{{ __('Create User Group') }}"
            description="{{ __('Get started by creating your first user group.') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout :topic="__('User Groups')" :url="Statamic::docsUrl('users#user-groups')" />
@endsection
