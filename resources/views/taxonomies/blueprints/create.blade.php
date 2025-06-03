@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Blueprint'))

@section('content')
    <blueprint-create-form
        route="{{ $action }}"
        icon="taxonomies"
    ></blueprint-create-form>
@stop
