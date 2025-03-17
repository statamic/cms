@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Configure Taxonomy'))

@section('content')

<taxonomy-edit-form
    :blueprint="{{ json_encode($blueprint) }}"
    :initial-values="{{ json_encode($values) }}"
    :meta="{{ json_encode($meta) }}"
    url="{{ cp_route('taxonomies.update', $taxonomy->handle()) }}"
></taxonomy-edit-form>

@stop
