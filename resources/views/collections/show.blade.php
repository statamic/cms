@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ $collection->title() }}</h1>

        <a href="{{ cp_route('collections.entries.create', $collection->path()) }}" class="btn">{{ __('Create Entry') }}</a>
    </div>

    <listing-placeholder
        url="{{ cp_route('collections.entries.index', $collection->path()) }}"
    ></listing-placeholder>

@endsection
