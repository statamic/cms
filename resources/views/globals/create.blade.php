@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Global Set'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <global-create-form route="{{ cp_route('globals.store') }}"></global-create-form>
@endsection
