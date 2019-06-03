@extends('statamic::layout')
@section('title', __('Create User'))

@section('content')
    <user-wizard
        route="{{ cp_route('users.store') }}">
    </user-wizard>
@stop
