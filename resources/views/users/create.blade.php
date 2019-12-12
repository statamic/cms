@extends('statamic::layout')
@section('title', __('Create User'))

@section('content')
    <user-wizard
        route="{{ cp_route('users.store') }}"
        users-index-url="{{ cp_route('users.index') }}"
        users-create-url="{{ cp_route('users.create') }}"
    >
    </user-wizard>
@stop
