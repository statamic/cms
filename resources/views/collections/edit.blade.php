@extends('statamic::layout')
@section('title', __('Configure Collection'))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('collections.show', $collection->handle()),
            'title' => $collection->title()
        ])
        <h1>@yield('title')</h1>
    </header>

    <collection-edit-form
        initial-title="{{ $collection->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('collections.update', $collection->handle()) }}"
        listing-url="{{ cp_route('collections.index')}}"
    ></collection-edit-form>

@stop
