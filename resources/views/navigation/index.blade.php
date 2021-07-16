@extends('statamic::layout')
@section('title', __('Navigation'))

@section('content')

    @unless($navs->isEmpty())

        <header class="flex items-center justify-between mb-3">
            <h1>{{ __('Navigation') }}</h1>

            @can('create', 'Statamic\Contracts\Structures\Nav')
                <a href="{{ cp_route('navigation.create') }}" class="btn-primary">{{ __('Create Navigation') }}</a>
            @endcan
        </header>

        <navigation-listing
            :initial-rows="{{ json_encode($navs) }}">
        </navigation-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('Navigation'),
            'description' => __('statamic::messages.navigation_configure_intro'),
            'svg' => 'empty/navigation',
            'button_text' => __('Create Navigation'),
            'button_url' => cp_route('navigation.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Structures\Nav')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Navigation'),
        'url' => Statamic::docsUrl('navigation')
    ])

@endsection
