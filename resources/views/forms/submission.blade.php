@extends('statamic::layout')
@section('title', Statamic::crumb('Submission ' . $submission->id(), $submission->form->title(), 'Forms'))

@section('content')
    <publish-form
        :title="$date.format('{{ $submission->date() }}')"
        :blueprint="{{ Js::from($blueprint) }}"
        :meta="{{ Js::from($meta) }}"
        :values="{{ Js::from($values) }}"
        read-only
    ></publish-form>
@endsection
