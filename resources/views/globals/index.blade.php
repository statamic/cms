@extends('statamic::layout')
@section('title', __('Global Sets'))

@section('content')

    @unless($globals->isEmpty())

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ __('Globals') }}</h1>
            @can('create', 'Statamic\Contracts\Globals\GlobalSet')
                <a href="{{ cp_route('globals.create') }}" class="btn-primary">{{ __('Create Global Set') }}</a>
            @endcan
        </div>

        <global-listing :globals="{{ json_encode($globals) }}"></global-listing>

    @else

        @include('statamic::partials.empty-state', [
            'resource' => 'Global Set',
            'description' => __('statamic::messages.global_set_config_intro'),
            'svg' => 'empty/content',
            'route' => cp_route('globals.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Globals\GlobalSet')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Global Variables'),
        'url' => Statamic::docsUrl('globals')
    ])

@endsection
