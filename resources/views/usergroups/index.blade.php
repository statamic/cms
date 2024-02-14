@extends('statamic::layout')
@section('title', Statamic\trans('User Groups'))

@section('content')

    @unless($groups->isEmpty())

        <div class="flex mb-6">
            <h1 class="flex-1">
                {{ Statamic\trans('User Groups') }}
            </h1>
            <a href="{{ cp_route('user-groups.create') }}" class="btn-primary">{{ Statamic\trans('Create User Group') }}</a>
        </div>

        <user-group-listing :initial-rows="{{ json_encode($groups) }}"></user-group-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => Statamic\trans('User Groups'),
            'description' => Statamic\trans('statamic::messages.user_groups_intro'),
            'svg' => 'empty/users',
            'button_text' => Statamic\trans('Create User Group'),
            'button_url' => cp_route('user-groups.create'),
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('User Groups'),
        'url' => Statamic::docsUrl('users#user-groups')
    ])

@endsection
