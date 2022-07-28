@extends('statamic::layout')
@section('title', __('User Groups'))

@section('content')

    @unless($groups->isEmpty())

        <header class="mb-3">
            <div class="flex flex-wrap items-center max-w-full gap-2">
                <h1 class="flex-1 break-words max-w-full">{{ __('User Groups') }}</h1>
                
                <a href="{{ cp_route('user-groups.create') }}" class="btn-primary">{{ __('Create User Group') }}</a>
            </div>
        </header>

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
