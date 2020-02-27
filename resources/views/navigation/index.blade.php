@extends('statamic::layout')
@section('title', __('Navigation'))

@section('content')

    @unless($navs->isEmpty())

        <header class="flex items-center justify-between mb-3">
            <h1>{{ __('Navigation') }}</h1>

            @can('create', 'Statamic\Contracts\Structures\Structure')
                <a href="{{ cp_route('navigation.create') }}" class="btn-primary">{{ __('Create Navigation') }}</a>
            @endcan
        </header>

        <navigation-listing
            :initial-rows="{{ json_encode($navs) }}">
        </navigation-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Navigation',
            'description' => 'Structures are hierarchical arrangements of your content, most often used to represent forms of site navigation.',
            'svg' => 'empty/structure',
            'route' => cp_route('navigation.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Structures\Nav')
        ])

    @endunless

@endsection
