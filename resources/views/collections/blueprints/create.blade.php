@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Blueprint'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <blueprint-create-form
        route="{{ $action }}"
        icon="collections"
    ></blueprint-create-form>
@stop
