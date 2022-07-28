@extends('statamic::layout')
@section('title', __('Users'))
@section('wrapper_class', 'max-w-full')

@section('content')

    <header class="mb-3">
        <div class="flex flex-wrap items-center max-w-full gap-2">
            <h1 class="flex-1 break-words max-w-full">{{ __('Users') }}</h1>

            @can('configure fields')
                <dropdown-list>
                    <dropdown-item :text="__('Edit Blueprint')" redirect="{{ cp_route('users.blueprint.edit') }}"></dropdown-item>
                </dropdown-list>
            @endcan

            @if (Statamic::pro() && $user->can('create', 'Statamic\Contracts\Auth\User'))
                <a href="{{ cp_route('users.create') }}" class="btn-primary">{{ __('Create User') }}</a>
            @endif
        </div>
    </header>

    <user-listing
        class="h-full"
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
