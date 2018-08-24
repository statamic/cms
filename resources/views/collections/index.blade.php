@extends('statamic::layout')

@section('content')

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

@endsection
