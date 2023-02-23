@extends('statamic::layout')
@section('title', __('Configure Taxonomy'))

@section('content')

    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('taxonomies.show', $taxonomy->handle()),
            'title' => $taxonomy->title()
        ])
        <div class="flex items-center justify-between">
            <h1>@yield('title')</h1>
            <button type="submit" class="btn-primary">{{ __('Save') }}</button>
        </div>
    </header>

    <taxonomy-edit-form
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('taxonomies.update', $taxonomy->handle()) }}"
    ></taxonomy-edit-form>

@stop
