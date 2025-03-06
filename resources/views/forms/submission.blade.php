@extends('statamic::layout')
@section('title', Statamic::crumb('Submission ' . $submission->id(), $submission->form->title(), 'Forms'))

@section('content')
    @include(
        'statamic::partials.breadcrumb',
        [
            'url' => cp_route('forms.show', $submission->form->handle()),
            'title' => $submission->form->title(),
        ]
    )

    <publish-form
        title="{{ $title }}"
        :blueprint="{{ Js::from($blueprint) }}"
        :meta="{{ Js::from($meta) }}"
        :values="{{ Js::from($values) }}"
        read-only
    ></publish-form>
@endsection
