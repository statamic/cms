@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('User Groups'))

@section('content')
    <ui-header title="{{ __('User Groups') }}" icon="groups">
        <ui-command-palette-item
            category="{{ Statamic\CommandPalette\Category::Actions }}"
            text="{{ __('Create User Group') }}"
            url="{{ cp_route('user-groups.create') }}"
            icon="groups"
            v-slot="{ text, url }"
        >
            <ui-button
                :href="url"
                variant="primary"
                :text="text"
            ></ui-button>
        </ui-command-palette-item>
    </ui-header>

    <user-group-listing :initial-rows="{{ json_encode($groups) }}"></user-group-listing>

    <x-statamic::docs-callout
        :topic="__('User Groups')"
        :url="Statamic::docsUrl('users#user-groups')"
    />
@endsection
