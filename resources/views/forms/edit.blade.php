@extends('statamic::layout')
@section('title', __('Configure Form'))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('forms.show', $form->handle()),
            'title' => $form->title()
        ])
        <h1>@yield('title')</h1>
    </header>

    <collection-edit-form
        initial-title="{{ $form->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('forms.update', $form->handle()) }}"
        listing-url="{{ cp_route('forms.index') }}"
    ></collection-edit-form>

@endsection
