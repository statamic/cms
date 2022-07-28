@extends('statamic::layout')
@section('title', __('Collections'))

@section('content')

    @unless($collections->isEmpty())
    
        <header class="mb-3">
            <div class="flex flex-wrap items-center max-w-full gap-2">
                <h1 class="flex-1 break-words max-w-full">{{ __('Collections') }}</h1>

                @can('create', 'Statamic\Contracts\Entries\Collection')
                    <a href="{{ cp_route('collections.create') }}" class="btn-primary">{{ __('Create Collection') }}</a>
                @endcan
            </div>
        </header>

        <collection-list
            :initial-rows="{{ json_encode($collections) }}"
            :initial-columns="{{ json_encode($columns) }}"
            :endpoints="{}">
        </collection-list>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('Collections'),
            'description' => __('statamic::messages.collection_configure_intro'),
            'svg' => 'empty/content',
            'button_text' => __('Create Collection'),
            'button_url' => cp_route('collections.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Entries\Collection')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Collections'),
        'url' => Statamic::docsUrl('collections-and-entries')
    ])

@endsection
