@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Roles & Permissions'))

@section('content')
    <ui-header title="{{ __('Roles & Permissions') }}" icon="permissions">
        <ui-command-palette-item
            category="{{ Statamic\CommandPalette\Category::Actions }}"
            text="{{ __('Create Role') }}"
            url="{{ cp_route('roles.create') }}"
            icon="permissions"
            prioritize
            v-slot="{ text, url }"
        >
            <ui-button
                :text="text"
                :href="url"
                variant="primary"
            ></ui-button>
        </ui-command-palette-item>
    </ui-header>

    <role-listing
        :initial-rows="{{ json_encode($roles) }}"
        :initial-columns="{{ json_encode($columns) }}"
    ></role-listing>

    <x-statamic::docs-callout
        :topic="__('Roles & Permissions')"
        :url="Statamic::docsUrl('users#permissions')"
    />
@endsection
