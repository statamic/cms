@extends('statamic::layout')
@section('title', Statamic\trans('Navigation'))

@section('content')

    @unless($navs->isEmpty())

        <header class="flex items-center justify-between mb-6">
            <h1>{{ Statamic\trans('Navigation') }}</h1>

            @can('create', 'Statamic\Contracts\Structures\Nav')
                <a href="{{ cp_route('navigation.create') }}" class="btn-primary">{{ Statamic\trans('Create Navigation') }}</a>
            @endcan
        </header>

        <navigation-listing
            :initial-rows="{{ json_encode($navs) }}">
        </navigation-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => Statamic\trans('Navigation'),
            'description' => Statamic\trans('statamic::messages.navigation_configure_intro'),
            'svg' => 'empty/navigation',
            'button_text' => Statamic\trans('Create Navigation'),
            'button_url' => cp_route('navigation.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Structures\Nav')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Navigation'),
        'url' => Statamic::docsUrl('navigation')
    ])

@endsection
