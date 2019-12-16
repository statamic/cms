@extends('statamic::layout')
@section('title', __('Create Collection'))

@section('content')
    <collection-wizard
        route="{{ cp_route('collections.store') }}">
    </collection-wizard>
@stop