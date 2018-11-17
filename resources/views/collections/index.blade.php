@extends('statamic::layout')

@section('content')

    @if(count($collections) == 0)
        <div class="text-center max-w-sm mx-auto pt-8 screen-centered">
            @svg('empty/collection')
            <h1 class="my-3">{{ __('Create your first Collection now') }}</h1>
            <p class="text-grey mb-3">
                {{ __('Collections are groups of entries that hold similar content and share behaviors and attributes.') }}
            </p>
            @can('super')
                <a href="{{ cp_route('forms.create') }}" class="btn-primary btn-lg">{{ __('Create Form') }}</a>
            @endcan
        </div>
    @endif

    @if(count($collections) > 0)

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Collections') }}</h1>

            @can('create', 'Statamic\Contracts\Data\Entries\Collection')
                <a href="{{ cp_route('collections.create') }}" class="btn">{{ __('Create Collection') }}</a>
            @endcan
        </div>

        <collection-list
            :initial-rows="{{ json_encode($collections) }}"
            :columns="{{ json_encode($columns) }}"
            :visible-columns="{{ json_encode($visibleColumns) }}"
            :endpoints="{}">
        </collection-list>
    @endif

@endsection
