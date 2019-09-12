@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')

    @unless($blueprints->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Blueprints') }}</h1>

            @can('create', 'Statamic\Fields\Blueprint')
                <a href="{{ cp_route('blueprints.create') }}" class="btn-primary">{{ __('Create Blueprint') }}</a>
            @endcan
        </div>

        <blueprint-listing :blueprints="{{ json_encode($blueprints) }}"></blueprint-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Blueprint',
            'description' => 'Blueprints let you mix and match fields and fieldsets to create the content structures for collections and other data types.',
            'svg' => 'empty/blueprints',
            'route' => cp_route('blueprints.create'),
            'can' => $user->can('create', 'Statamic\Fields\Blueprint')
        ])

    @endunless

@endsection
