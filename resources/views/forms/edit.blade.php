@extends('statamic::layout')
@section('title', __('Edit Form'))

@section('content')

    <collection-edit-form
        initial-title="{{ $form->title() }}"
        parent-title="{{ __('Forms') }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('forms.update', $form->handle()) }}"
        listing-url="{{ cp_route('forms.index')}}"
    ></collection-edit-form>

@endsection
