@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Blueprints') }}</h1>

        @can('create', 'Statamic\Fields\Blueprint')
            <a href="{{ cp_route('blueprints.create') }}" class="btn">{{ __('Create Blueprint') }}</a>
        @endcan
    </div>

    <blueprint-listing :blueprints="{{ json_encode($blueprints) }}"></blueprint-listing>

@endsection
