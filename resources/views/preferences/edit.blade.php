@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', $title)

@section('content')

<preferences-edit-form
    title="{{ $title }}"
    :blueprint="{{ Js::from($blueprint) }}"
    :meta="{{ Js::from($meta) }}"
    :values="{{ Js::from($values) }}"
    action="{{ $actionUrl }}"
    :save-as-options="{{ json_encode($saveAsOptions) }}"
></preferences-edit-form>

@stop
