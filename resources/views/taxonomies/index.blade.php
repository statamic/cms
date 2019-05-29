@extends('statamic::layout')
@section('title', __('Taxonomies'))

@section('content')

    @unless($taxonomies->isEmpty())

        {{-- Todo --}}

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Taxonomy',
            'description' => 'A Taxonomy is a system of classifying data around a set of unique characteristics, such as category or color.',
            'svg' => 'empty/collection',
            'route' => cp_route('taxonomies.create')
        ])

    @endunless

@endsection
