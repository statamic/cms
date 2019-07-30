@extends('statamic::layout')
@section('title', __('Edit Collection'))

@section('content')

    <taxonomy-edit-form
        initial-title="{{ $taxonomy->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('taxonomies.update', $taxonomy->handle()) }}"
        listing-url="{{ cp_route('taxonomies.index')}}"
    ></taxonomy-edit-form>

@stop
