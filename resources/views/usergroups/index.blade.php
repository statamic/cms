@extends('statamic::layout')
@section('title', __('User Groups'))

@section('content')

    @unless($groups->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">
                {{ __('User Groups') }}
            </h1>
            <a href="{{ cp_route('user-groups.create') }}" class="btn-primary">{{ __('Create User Group') }}</a>
        </div>

        <user-group-listing :initial-rows="{{ json_encode($groups) }}"></user-group-listing>

    @else

        @include('statamic::partials.empty-state', [
            'resource' => 'User Group',
            'description' => __('statamic::messages.user_groups_intro'),
            'svg' => 'empty/users',
            'route' => cp_route('user-groups.create')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('User Groups'),
        'url' => 'users#user-groups'
    ])

@endsection
