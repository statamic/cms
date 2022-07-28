@extends('statamic::layout')
@section('title', __('Global Sets'))

@section('content')

    @unless($globals->isEmpty())

        <header class="mb-3">
            <div class="flex flex-wrap items-center max-w-full gap-2">
                <h1 class="flex-1 break-words max-w-full">{{ __('Globals') }}</h1>

                @can('create', 'Statamic\Contracts\Globals\GlobalSet')
                    <a href="{{ cp_route('globals.create') }}" class="btn-primary">{{ __('Create Global Set') }}</a>
                @endcan
            </div>
        </header>

        <global-listing
            :globals="{{ json_encode($globals) }}">
        </global-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('Globals'),
            'description' => __('statamic::messages.global_set_config_intro'),
            'svg' => 'empty/content',
            'button_text' => __('Create Global Set'),
            'button_url' => cp_route('globals.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Globals\GlobalSet')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Global Variables'),
        'url' => Statamic::docsUrl('globals')
    ])

@endsection
