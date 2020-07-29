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
            'title' => __('User Groups'),
            'description' => __('statamic::messages.user_groups_intro'),
            'svg' => 'empty/users',
            'button_text' => __('Create User Group'),
            'button_url' => cp_route('user-groups.create'),
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('User Groups'),
        'url' => Statamic::docsUrl('users#user-groups')
    ])

@endsection
