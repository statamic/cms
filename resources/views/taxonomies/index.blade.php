@extends('statamic::layout')
@section('title', Statamic\trans('Taxonomies'))

@section('content')

    @unless($taxonomies->isEmpty())

        <div class="flex mb-6">
            <h1 class="flex-1">{{ Statamic\trans('Taxonomies') }}</h1>

            @can('create', 'Statamic\Contracts\Taxonomies\Taxonomy')
                <a href="{{ cp_route('taxonomies.create') }}" class="btn-primary">{{ Statamic\trans('Create Taxonomy') }}</a>
            @endcan
        </div>

        <taxonomy-list
            :initial-rows="{{ json_encode($taxonomies) }}"
            :initial-columns="{{ json_encode($columns) }}"
            :endpoints="{}">
        </taxonomy-list>

    @else

        @include('statamic::partials.empty-state', [
            'title' => Statamic\trans('Taxonomies'),
            'description' => Statamic\trans('statamic::messages.taxonomy_configure_intro'),
            'svg' => 'empty/taxonomy',
            'button_text' => Statamic\trans('Create Taxonomy'),
            'button_url' => cp_route('taxonomies.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Taxonomies\Taxonomy')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Taxonomies'),
        'url' => Statamic::docsUrl('taxonomies')
    ])

@endsection
