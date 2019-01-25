@extends('statamic::layout')

@section('content')
    <div class="flex mb-3">
        <h1 class="flex-1">
            <small class="subhead block">
                <a href="{{ cp_route('collections.index')}}">{{ __('Collections') }}</a>
            </small>
            {{ $collection->title() }}
        </h1>
        <create-entry-button
            url="{{ cp_route('collections.entries.create', $collection->handle()) }}"
            :blueprints="{{ $blueprints->toJson() }}"
        ></create-entry-button>
    </div>

    <entry-list
        collection="{{ $collection->handle() }}"
        initial-sort-column="title"
        initial-sort-direction="asc"
    ></entry-list>

@endsection
