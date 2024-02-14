@extends('statamic::layout')
@section('title', Statamic\trans('Global Sets'))

@section('content')

    @unless($globals->isEmpty())

        <div class="flex items-center mb-6">
            <h1 class="flex-1">{{ Statamic\trans('Globals') }}</h1>
            @can('create', 'Statamic\Contracts\Globals\GlobalSet')
                <a href="{{ cp_route('globals.create') }}" class="btn-primary">{{ Statamic\trans('Create Global Set') }}</a>
            @endcan
        </div>

        <global-listing :globals="{{ json_encode($globals) }}"></global-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => Statamic\trans('Globals'),
            'description' => Statamic\trans('statamic::messages.global_set_config_intro'),
            'svg' => 'empty/globals',
            'button_url' => cp_route('globals.create'),
            'button_text' => Statamic\trans('Create Global Set'),
            'can' => $user->can('create', 'Statamic\Contracts\Globals\GlobalSet')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Global Variables'),
        'url' => Statamic::docsUrl('globals')
    ])

@endsection
