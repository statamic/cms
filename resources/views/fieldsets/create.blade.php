@extends('statamic::layout')
@section('title', Statamic\trans('Create Fieldset'))

@section('content')
    <fieldset-create-form
        route="{{ cp_route('fieldsets.store') }}">
    </fieldset-create-form>
@stop
