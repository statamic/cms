@extends('statamic::layout')
@section('title', __('Fieldsets'))

@section('content')

    @unless($fieldsets->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Fieldsets') }}</h1>

            @can('create', 'Statamic\Fields\Fieldset')
                <a href="{{ cp_route('fieldsets.create') }}" class="btn-primary">{{ __('Create Fieldset') }}</a>
            @endcan
        </div>

        <fieldset-listing :fieldsets="{{ json_encode($fieldsets) }}"></fieldset-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Fieldset',
            'description' => 'Fieldsets are an optional companion to blueprints, allowing you to create partials to be used within blueprints.',
            'svg' => 'empty/collection', // TODO: Need empty/fieldset svg
            'route' => cp_route('fieldsets.create'),
            'can' => $user->can('create', 'Statamic\Fields\Fieldset')
        ])

    @endunless

@endsection
