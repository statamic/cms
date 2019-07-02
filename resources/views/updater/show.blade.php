@extends('statamic::layout')
@section('title', __('Updater'))

@section('content')
    <updater slug="{{ $slug }}" package="{{ $package }}"></updater>
@endsection
