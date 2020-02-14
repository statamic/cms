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

        @include('statamic::partials.create-first', [
            'resource' => 'Global Set',
            'description' => 'Global Sets contain content available across the entire site, like company details, contact information, or front-end settings.',
            'svg' => 'empty/global',
            'route' => cp_route('globals.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Globals\GlobalSet')
        ])

    @endunless

@endsection
