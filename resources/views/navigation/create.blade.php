@extends('statamic::layout')
@section('title', Statamic\trans('Create Navigation'))

@section('content')
    <navigation-create-form
        route="{{ cp_route('navigation.store') }}">
    </navigation-create-form>
@stop
