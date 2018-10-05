@extends('statamic::layout')

@section('content')
    <updater :ajax-timeout="{{ config('statamic.system.ajax_timeout') }}"></updater>
@endsection
