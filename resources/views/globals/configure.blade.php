@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Configure Global Set'))

@section('content')
<global-edit-form
    :blueprint="{{ json_encode($blueprint) }}"
    :initial-values="{{ json_encode($values) }}"
    :meta="{{ json_encode($meta) }}"
    url="{{ cp_route('globals.update', $set->id()) }}"
></global-edit-form>
@stop
