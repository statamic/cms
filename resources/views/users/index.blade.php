@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Users'))
@section('wrapper_class', 'max-w-full')

@section('content')

    <header class="flex items-center mb-6">
        <h1 class="flex-1">
            {{ __('Users') }}
        </h1>

        @can('configure fields')
            <dropdown-list class="rtl:ml-2 ltr:mr-2">
                <dropdown-item :text="__('Edit Blueprint')" redirect="{{ cp_route('users.blueprint.edit') }}"></dropdown-item>
            </dropdown-list>
        @endcan

        @if (Statamic::pro() && $user->can('create', 'Statamic\Contracts\Auth\User'))
            <a href="{{ cp_route('users.create') }}" class="btn-primary rtl:mr-4 ltr:ml-4">{{ __('Create User') }}</a>
        @endif
    </header>

    <user-listing
        listing-key="users"
        initial-sort-column="{{ config('statamic.users.sort_field', 'email') }}"
        initial-sort-direction="{{ config('statamic.users.sort_direction', 'asc') }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route('users.actions.run') }}"
    ></user-listing>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Users'),
        'url' => Statamic::docsUrl('users')
    ])

@endsection
