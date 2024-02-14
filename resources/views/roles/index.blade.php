@extends('statamic::layout')
@section('title', Statamic\trans('Roles'))

@section('content')

    @unless($roles->isEmpty())

        <header class="flex items-center justify-between mb-6">
            <h1>{{ Statamic\trans('Roles & Permissions') }}</h1>
            <a href="{{ cp_route('roles.create') }}" class="btn-primary">{{ Statamic\trans('Create Role') }}</a>
        </header>

        <role-listing
            :initial-rows="{{ json_encode($roles) }}"
            :initial-columns="{{ json_encode($columns) }}">
        </role-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => Statamic\trans('Roles & Permissions'),
            'description' => Statamic\trans('statamic::messages.role_intro'),
            'svg' => 'empty/users',
            'button_text' => Statamic\trans('Create Role'),
            'button_url' => cp_route('roles.create'),
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Roles & Permissions'),
        'url' => Statamic::docsUrl('users#permissions')
    ])

@endsection
