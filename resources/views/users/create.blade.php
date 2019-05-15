@extends('statamic::layout')

@section('content')
    <user-wizard
        route="{{ cp_route('users.store') }}">
    </user-wizard>
@stop