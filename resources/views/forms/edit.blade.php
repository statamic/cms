@extends('statamic::layout')
@section('title', __('Configure Form'))

@section('content')

    <collection-edit-form
        initial-title="{{ $form->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('forms.update', $form->handle()) }}"
        listing-url="{{ cp_route('forms.index') }}"
    ></collection-edit-form>

@endsection
