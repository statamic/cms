@extends('statamic::layout')
@section('title', __('Create Taxonomy'))

@section('content')

    <taxonomy-wizard
        route="{{ cp_route('taxonomies.store') }}">
    </taxonomy-wizard>

@endsection
