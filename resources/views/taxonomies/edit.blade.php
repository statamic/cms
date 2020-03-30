@extends('statamic::layout')
@section('title', __('Configure Taxonomy'))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('taxonomies.show', $taxonomy->handle()),
            'title' => $taxonomy->title()
        ])
        <h1>@yield('title')</h1>
    </header>

    <taxonomy-edit-form
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('taxonomies.update', $taxonomy->handle()) }}"
    ></taxonomy-edit-form>

@stop
