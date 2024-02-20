@extends('statamic::layout')
@section('title', __('Create Global Set'))
@section('content')

    <global-create-form
        route="{{ cp_route('globals.store') }}">
    </global-create-form>

@endsection
