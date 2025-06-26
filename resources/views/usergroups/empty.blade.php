@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('User Groups'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2">
            <ui-icon name="collections" class="size-5 text-gray-500"></ui-icon>
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

    <x-statamic::docs-callout
        :topic="__('User Groups')"
        :url="Statamic::docsUrl('users#user-groups')"
    />
@endsection
