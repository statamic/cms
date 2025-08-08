@extends('statamic::layout')
@section('title', Statamic::crumb('Submission ' . $submission->id(), $submission->form->title(), 'Forms'))

@section('content')
    <ui-publish-form
        icon="forms"
        :title="$date.format('{{ $submission->date() }}')"
        :blueprint="{{ Js::from($blueprint) }}"
        :initial-values="{{ Js::from($values) }}"
        :initial-meta="{{ Js::from($meta) }}"
        :submit-url="null"
        read-only
    ></ui-publish-form>
@endsection
