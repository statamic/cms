@extends('statamic::layout')
@section('title', __('Edit Navigation'))

@section('content')

    <structure-edit-form
        initial-title="{{ $structure->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('structures.update', $structure->handle()) }}"
        listing-url="{{ cp_route('structures.index')}}"
    ></structure-edit-form>

@endsection
