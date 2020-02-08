@extends('statamic::layout')
@section('title', __('Scaffold Collection'))

@section('content')
    <h1 class="flex-1 mb-3">
        <small class="subhead block">
            <a href="{{ cp_route('collections.index')}}">{{ __('Collections') }}</a>
            <span class="px-sm">›</span>
            <a href="{{ cp_route('collections.show', $collection->handle()) }}">{{ $collection->title() }}</a>
        </small>
        {{ __('Scaffold Resources') }}
    </h1>

    <collection-scaffolder
        title="{{ $collection->title() }}"
        handle="{{ $collection->handle() }}"
        route="{{ url()->current() }}" >
    </collection-scaffolder>
@stop
