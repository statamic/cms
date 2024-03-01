@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Configure Sites'))

@section('content')

    <sites-edit-form
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('sites.update') }}"
    ></sites-edit-form>

@stop
