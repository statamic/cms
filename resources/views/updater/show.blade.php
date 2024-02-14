@extends('statamic::layout')
@section('title', Statamic\trans('Updater'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('updater'),
        'title' => Statamic\trans('Updates')
    ])

    <updater slug="{{ $slug }}" package="{{ $package }}" name="{{ $name }}"></updater>

@endsection
