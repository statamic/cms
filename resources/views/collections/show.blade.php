@extends('statamic::layout')

@section('content')
    <div class="flex mb-3">
        <h1 class="flex-1">
            <a href="{{ cp_route('collections.index')}}">{{ __('Collections') }}</a>
            @svg('chevron-right')
            {{ $collection->title() }}
        </h1>
        <a href="{{ cp_route('collections.entries.create', $collection->handle()) }}" class="btn btn-primary">{{ __('Create Entry') }}</a>
    </div>

    <entry-list
        collection="{{ $collection->handle() }}"
        initial-sort-column="title"
        initial-sort-direction="asc"
    ></entry-list>

@endsection
