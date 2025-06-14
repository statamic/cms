@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Scaffold Collection'))

@section('content')
<header class="mb-6">
    <h1>{{ __('Scaffold Views') }}</h1>
</header>

<collection-scaffolder
    title="{{ $collection->title() }}"
    handle="{{ $collection->handle() }}"
    route="{{ url()->current() }}"
></collection-scaffolder>
@stop
