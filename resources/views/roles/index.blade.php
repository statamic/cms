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
            'title' => __('Roles & Permissions'),
            'description' => __('statamic::messages.role_intro'),
            'svg' => 'empty/users',
            'button_text' => __('Create Role'),
            'button_url' => cp_route('roles.create'),
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Roles & Permissions'),
        'url' => Statamic::docsUrl('users#permissions')
    ])

@endsection
