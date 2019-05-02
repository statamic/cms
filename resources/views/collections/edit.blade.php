@extends('statamic::layout')

@section('content')

    <collection-edit-form
        initial-title="{{ $collection->title() }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('collections.update', $collection->handle()) }}"
    ></collection-edit-form>

@stop
