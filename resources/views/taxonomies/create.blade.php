@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Taxonomy'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <taxonomy-create-form route="{{ cp_route('taxonomies.store') }}"></taxonomy-create-form>
@endsection
