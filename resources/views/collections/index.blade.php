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
            :initial-columns="{{ json_encode($columns) }}"
            :endpoints="{}">
        </collection-list>

    @else

        @include('statamic::partials.empty-state', [
            'resource' => 'Collection',
            'description' => __('statamic::messages.collection_configure_intro'),
            'docs_link' => Statamic::docsUrl('collections-and-entries'),
            'svg' => 'empty/content',
            'route' => cp_route('collections.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Entries\Collection')
        ])

    @endunless

@endsection
