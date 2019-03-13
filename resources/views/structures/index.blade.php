@extends('statamic::layout')

@section('content')

    @if (! count($structures))
        <div class="no-results text-center max-w-md mx-auto mt-5 screen-centered border-2 border-dashed rounded-lg px-4 py-8">
            @svg('empty/structure')
            <h1 class="my-3">{{ __('Create your first Structure now') }}</h1>
            <p class="text-grey mb-3">
                {{ __('Structures are heirarchial arrangements of your content, most often used to represent forms of site navigation.') }}
            </p>
            @can('create', 'Statamic\Contracts\Data\Structures\Structure')
                <a href="{{ cp_route('structures.create') }}" class="btn-primary btn-lg">{{ __('Create Structure') }}</a>
            @endcan
        </div>
    @endif

    @if(count($structures) > 0)

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Structures') }}</h1>

            @can('create', 'Statamic\Contracts\Data\Structures\Structure')
                <a href="{{ cp_route('collections.create') }}" class="btn-primary">{{ __('Create Structure') }}</a>
            @endcan
        </div>

        <structure-listing
            :initial-rows="{{ json_encode($structures) }}">
        </structure-listing>
    @endif

@endsection
