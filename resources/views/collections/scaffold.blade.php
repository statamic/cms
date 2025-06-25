@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Scaffold Views'))

@section('content')
    <ui-header :title="__('Scaffold Views')" icon="scaffold" />
    <collection-scaffolder
        title="{{ $collection->title() }}"
        handle="{{ $collection->handle() }}"
        route="{{ url()->current() }}"
    ></collection-scaffolder>
@stop
