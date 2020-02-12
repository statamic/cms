@extends('statamic::layout')
@section('title', __('Collections'))

@section('content')

    @unless($collections->isEmpty())

        <div class="flex items-center justify-between mb-3">
            <h1>{{ __('Collections') }}</h1>

            @can('create', 'Statamic\Contracts\Entries\Collection')
                <a href="{{ cp_route('collections.create') }}" class="btn-primary">{{ __('Create Collection') }}</a>
            @endcan
        </div>

        <collection-list
            :initial-rows="{{ json_encode($collections) }}"
            :columns="{{ json_encode($columns) }}"
            :endpoints="{}">
        </collection-list>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Collection',
            'description' => 'Collections are groups of entries that hold similar content and share behaviors and attributes.',
            'svg' => 'empty/collection',
            'route' => cp_route('collections.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Entries\Collection')
        ])

    @endunless

@endsection
