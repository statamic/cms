@extends('statamic::layout')

@section('content')
    <structure-wizard
        route="{{ cp_route('structures.store') }}">
    </structure-wizard>
@stop
