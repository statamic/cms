@extends('statamic::layout')
@section('title', __('Roles'))

@section('content')

    @unless($roles->isEmpty())

        <header class="flex items-center justify-between mb-3">
            <h1>{{ __('Roles & Permissions') }}</h1>
            <a href="{{ cp_route('roles.create') }}" class="btn-primary">{{ __('Create Role') }}</a>
        </header>

        <role-listing
            :initial-rows="{{ json_encode($roles) }}"
            :initial-columns="{{ json_encode($columns) }}">
        </role-listing>

    @else

        @include('statamic::partials.empty-state', [
            'resource' => 'Role',
            'description' => __('statamic::messages.role_intro'),
            'docs_link' => Statamic::docsUrl('users#permissions'),
            'svg' => 'empty/users',
            'route' => cp_route('roles.create')
        ])

    @endunless

@endsection
