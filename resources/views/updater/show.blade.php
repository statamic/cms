@extends('statamic::layout')
@section('title', __('Updater'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('updater'),
        'title' => __('Updates')
    ])

    <updater slug="{{ $slug }}" package="{{ $package }}" name="{{ $name }}"></updater>

@endsection
