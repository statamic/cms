@extends('statamic::layout')
@section('title', Statamic\trans('Collections'))

@section('content')

    @unless($collections->isEmpty())

        <div class="flex items-center justify-between mb-6">
            <h1>{{ Statamic\trans('Collections') }}</h1>

            @can('create', 'Statamic\Contracts\Entries\Collection')
                <a href="{{ cp_route('collections.create') }}" class="btn-primary">{{ Statamic\trans('Create Collection') }}</a>
            @endcan
        </div>

        <collection-list
            :initial-rows="{{ json_encode($collections) }}"
            :initial-columns="{{ json_encode($columns) }}"
            :endpoints="{}">
        </collection-list>

    @else

        @include('statamic::partials.empty-state', [
            'title' => Statamic\trans('Collections'),
            'description' => Statamic\trans('statamic::messages.collection_configure_intro'),
            'svg' => 'empty/content',
            'button_text' => Statamic\trans('Create Collection'),
            'button_url' => cp_route('collections.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Entries\Collection')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Collections'),
        'url' => Statamic::docsUrl('collections-and-entries')
    ])

@endsection
