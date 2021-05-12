@extends('statamic::layout')
@section('title', Statamic::crumb('Submission ' . $submission->id(), $submission->form->title(), 'Forms'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('forms.show', $submission->form->handle()),
        'title' => $submission->form->title()
    ])

    <publish-form
        title="{{ $title }}"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        read-only
    ></publish-form>

@endsection
