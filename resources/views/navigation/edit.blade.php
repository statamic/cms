@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Edit Navigation'))

@section('content')
<navigation-edit-form
    :blueprint="{{ json_encode($blueprint) }}"
    :initial-values="{{ json_encode($values) }}"
    :meta="{{ json_encode($meta) }}"
    url="{{ $nav->showUrl() }}"
></navigation-edit-form>
@stop
