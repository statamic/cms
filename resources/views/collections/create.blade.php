@extends('statamic::layout')
@section('title', __('Create Collection'))

@section('content')
    <collection-create-form
        route="{{ cp_route('collections.store') }}">
    </collection-create-form>
@stop
