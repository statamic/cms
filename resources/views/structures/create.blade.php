@extends('statamic::layout')
@section('title', __('Create Structure'))

@section('content')
    <structure-wizard
        route="{{ cp_route('structures.store') }}">
    </structure-wizard>
@stop
