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

        @include('statamic::partials.create-first', [
            'resource' => 'Taxonomy',
            'description' => 'A Taxonomy is a system of classifying data around a set of unique characteristics, such as category or color.',
            'svg' => 'empty/collection',
            'route' => cp_route('taxonomies.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Taxonomies\Taxonomy')
        ])

    @endunless

@endsection
