@extends('statamic::layout')
@section('title', Statamic\trans('Create Taxonomy'))

@section('content')

    <taxonomy-create-form
        route="{{ cp_route('taxonomies.store') }}">
    </taxonomy-create-form>

@endsection
