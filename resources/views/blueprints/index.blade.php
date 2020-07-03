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

        <blueprint-listing :initial-rows="{{ json_encode($blueprints) }}"></blueprint-listing>

    @else

        @include('statamic::partials.empty-state', [
            'resource' => 'Blueprint',
            'description' => __('statamic::messages.blueprints_intro'),
            'svg' => 'empty/form',
            'route' => cp_route('blueprints.create'),
            'can' => $user->can('create', 'Statamic\Fields\Blueprint')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
