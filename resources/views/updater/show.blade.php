@extends('statamic::layout')
@section('title', __('Updater'))

@section('content')

    @include('statamic::updater.partials.header', ['product' => $name])

    <updater slug="{{ $slug }}" package="{{ $package }}" name="{{ $name }}"></updater>

@endsection
