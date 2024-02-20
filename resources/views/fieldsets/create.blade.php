@extends('statamic::layout')
@section('title', __('Create Fieldset'))

@section('content')
    <fieldset-create-form
        route="{{ cp_route('fieldsets.store') }}">
    </fieldset-create-form>
@stop
