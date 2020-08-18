@extends('statamic::layout')
@section('title', __('Create Taxonomy'))

@section('content')

    <taxonomy-create-form
        route="{{ cp_route('taxonomies.store') }}">
    </taxonomy-create-form>

@endsection
