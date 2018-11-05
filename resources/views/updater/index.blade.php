@extends('statamic::layout')

@section('content')
    <updater slug="{{ $slug }}" package="{{ $package }}"></updater>
@endsection
