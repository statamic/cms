@extends('statamic::layout')
@section('title', __('Taxonomies'))

@section('content')

    @unless($taxonomies->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Taxonomies') }}</h1>

            @can('create', 'Statamic\Contracts\Taxonomies\Taxonomy')
                <a href="{{ cp_route('taxonomies.create') }}" class="btn-primary">{{ __('Create Taxonomy') }}</a>
            @endcan
        </div>

        <taxonomy-list
            :initial-rows="{{ json_encode($taxonomies) }}"
            :initial-columns="{{ json_encode($columns) }}"
            :endpoints="{}">
        </taxonomy-list>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('Taxonomies'),
            'description' => __('statamic::messages.taxonomy_configure_intro'),
            'svg' => 'empty/taxonomy',
            'button_text' => __('Create Taxonomy'),
            'button_url' => cp_route('taxonomies.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Taxonomies\Taxonomy')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Taxonomies'),
        'url' => Statamic::docsUrl('taxonomies')
    ])

@endsection
