@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Scaffold Views'))

@section('content')
<collection-scaffolder
    title="{{ $collection->title() }}"
    handle="{{ $collection->handle() }}"
    route="{{ url()->current() }}"
></collection-scaffolder>
@stop
