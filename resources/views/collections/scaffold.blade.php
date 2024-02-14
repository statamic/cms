@extends('statamic::layout')
@section('title', Statamic\trans('Scaffold Collection'))

@section('content')
    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('collections.show', $collection->handle()),
            'title' => $collection->title()
        ])
        <h1> {{ Statamic\trans('Scaffold Views') }}</h1>
    </header>

    <collection-scaffolder
        title="{{ $collection->title() }}"
        handle="{{ $collection->handle() }}"
        route="{{ url()->current() }}" >
    </collection-scaffolder>
@stop
