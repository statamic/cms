@extends('statamic::layout')
@section('title', __('Edit Navigation'))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('navigation.show', $nav->handle()),
            'title' => $nav->title()
        ])
        <h1>@yield('title')</h1>
    </header>

    <navigation-edit-form
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ $nav->showUrl() }}"
    ></navigation-edit-form>

@endsection
