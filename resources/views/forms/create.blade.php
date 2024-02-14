@extends('statamic::layout')
@section('title', Statamic\trans('Create Form'))

@section('content')
    <form-create-form
        route="{{ cp_route('forms.store') }}">
    </form-create-form>
@stop
