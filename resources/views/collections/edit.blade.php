@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Configure Collection'))

@section('content')
    <collection-edit-form
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('collections.update', $collection->handle()) }}"
    ></collection-edit-form>
@stop
