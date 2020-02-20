@extends('statamic::layout')
@section('title', __('Navigation'))

@section('content')

    @unless($structures->isEmpty())

        <header class="flex items-center justify-between mb-3">
            <h1>{{ __('Navigation') }}</h1>

            @can('create', 'Statamic\Contracts\Structures\Structure')
                <a href="{{ cp_route('structures.create') }}" class="btn-primary">{{ __('Create Navigation') }}</a>
            @endcan
        </header>

        <structure-listing
            :initial-rows="{{ json_encode($structures) }}">
        </structure-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Structure',
            'description' => 'Structures are hierarchical arrangements of your content, most often used to represent forms of site navigation.',
            'svg' => 'empty/structure',
            'route' => cp_route('structures.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Structures\Structure')
        ])

    @endunless

@endsection
