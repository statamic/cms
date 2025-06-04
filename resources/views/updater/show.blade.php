@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Updater'))

@section('content')
    <updater slug="{{ $slug }}" package="{{ $package }}" name="{{ $name }}"></updater>
@endsection
