@extends('statamic::layout')
@section('title', __('Users'))
@section('wrapper_class', 'max-w-full')

@section('content')

    <header class="flex items-center mb-3">
        <h1 class="flex-1">
            {{ __('Users') }}
        </h1>

        @can('configure fields')
            <dropdown-list class="mr-1">
                <dropdown-item :text="__('Edit Blueprint')" redirect="{{ cp_route('users.blueprint.edit') }}"></dropdown-item>
            </dropdown-list>
        @endcan

        @if (Statamic::pro() && $user->can('create', 'Statamic\Contracts\Auth\User'))
            <a href="{{ cp_route('users.create') }}" class="btn-primary ml-2">{{ __('Create User') }}</a>
        @endif
    </header>

    <user-listing
        listing-key="users"
        initial-sort-column="email"
        initial-sort-direction="asc"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route('users.actions.run') }}"
    ></user-listing>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Users'),
        'url' => Statamic::docsUrl('users')
    ])

@endsection
