@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Users'))

@section('content')
    <ui-header title="{{ __('Users') }}" icon="users">

        @can('configure fields')
            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                text="{{ __('Edit User Blueprint') }}"
                url="{{ cp_route('blueprints.users.edit') }}"
                icon="blueprints"
                v-slot="{ text, url }"
            >
                <ui-button
                    :text="text"
                    :href="url"
                ></ui-button>
            </ui-command-palette-item>
        @endcan

        @if (Statamic::pro() && $user->can('create', 'Statamic\Contracts\Auth\User'))
            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                prioritize
                text="{{ __('Create User') }}"
                url="{{ cp_route('users.create') }}"
                icon="users"
                v-slot="{ text, url }"
            >
                <ui-button
                    :text="text"
                    :href="url"
                    variant="primary"
                ></ui-button>
            </ui-command-palette-item>
        @endif
    </ui-header>

    <user-listing
        initial-sort-column="{{ config('statamic.users.sort_field', 'email') }}"
        initial-sort-direction="{{ config('statamic.users.sort_direction', 'asc') }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route('users.actions.run') }}"
    ></user-listing>

    <x-statamic::docs-callout
        :topic="__('Users')"
        :url="Statamic::docsUrl('users')"
    />
@endsection
