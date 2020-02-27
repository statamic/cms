@extends('statamic::layout')
@section('title', __('Edit Navigation'))

@section('content')

    <navigation-edit-form
        initial-title="{{ $nav->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ $nav->showUrl() }}"
        listing-url="{{ cp_route('navigation.index') }}"
    ></navigation-edit-form>

@endsection
