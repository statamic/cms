@extends('statamic::layout')
@section('title', __('Create Navigation'))

@section('content')
    <structure-create-form
        route="{{ cp_route('structures.store') }}">
    </structure-create-form>
@stop
