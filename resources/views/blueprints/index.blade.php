@extends('statamic::layout')

@section('content')

    @unless($blueprints->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Blueprints') }}</h1>

            @can('create', 'Statamic\Fields\Blueprint')
                <a href="{{ cp_route('blueprints.create') }}" class="btn">{{ __('Create Blueprint') }}</a>
            @endcan
        </div>

        <blueprint-listing :blueprints="{{ json_encode($blueprints) }}"></blueprint-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Blueprint',
            'description' => 'Blueprints define which sections and fields you see in a publish form.',
            'svg' => 'empty/collection', // TODO: Need empty/blueprint svg
            'route' => cp_route('blueprints.create')
        ])

    @endunless

@endsection
