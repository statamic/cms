@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Users'))

@section('content')
    <ui-header title="{{ __('Users') }}" icon="users">

        @can('configure fields')
            <ui-button
                :text="__('Edit User Blueprint')"
                href="{{ cp_route('users.blueprint.edit') }}"
            ></ui-button>
        @endcan

        @if (Statamic::pro() && $user->can('create', 'Statamic\Contracts\Auth\User'))
            <ui-button
                href="{{ cp_route('users.create') }}"
                variant="primary"
                :text="__('Create User')"
            ></ui-button>
        @endif
    </ui-header>

    <user-listing
        listing-key="users"
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
