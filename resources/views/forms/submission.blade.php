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

    <submission-publish-form
        date="{{ $submission->date()->toIso8601String() }}"
        :blueprint="{{ Js::from($blueprint) }}"
        :meta="{{ Js::from($meta) }}"
        :values="{{ Js::from($values) }}"
        read-only
    ></submission-publish-form>
@endsection
