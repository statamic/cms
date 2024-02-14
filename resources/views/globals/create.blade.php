@extends('statamic::layout')
@section('title', Statamic\trans('Create Global Set'))
@section('content')

    <global-create-form
        route="{{ cp_route('globals.store') }}">
    </global-create-form>

@endsection
