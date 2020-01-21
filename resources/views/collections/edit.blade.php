@extends('statamic::layout')
@section('title', __('Edit Collection'))

@section('content')

    <collection-edit-form
        initial-title="{{ $collection->title() }}"
        parent-title="{{ __('Collections') }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('collections.update', $collection->handle()) }}"
        listing-url="{{ cp_route('collections.index')}}"
    ></collection-edit-form>

@stop
