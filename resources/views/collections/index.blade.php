@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Collections'))

@section('content')
    @unless ($collections->isEmpty())
        <div class="mb-6 flex items-center justify-between">
            <h1>{{ __('Collections') }}</h1>

            @can('create', 'Statamic\Contracts\Entries\Collection')
                <a href="{{ cp_route('collections.create') }}" class="btn-primary">{{ __('Create Collection') }}</a>
            @endcan
        </div>

        <collection-list
            :initial-rows="{{ json_encode($collections) }}"
            :initial-columns="{{ json_encode($columns) }}"
        ></collection-list>
    @else
        @include(
            'statamic::partials.empty-state',
            [
                'title' => __('Collections'),
                'description' => __('statamic::messages.collection_configure_intro'),
                'svg' => 'empty/content',
                'button_text' => __('Create Collection'),
                'button_url' => cp_route('collections.create'),
                'can' => $user->can('create', 'Statamic\Contracts\Entries\Collection'),
            ]
        )
    @endunless

    @include(
        'statamic::partials.docs-callout',
        [
            'topic' => __('Collections'),
            'url' => Statamic::docsUrl('collections'),
        ]
    )
@endsection
