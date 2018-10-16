@extends('statamic::layout')

@section('content')
    <updater package="statamic/cms" :ajax-timeout="{{ config('statamic.system.ajax_timeout') }}"></updater>
@endsection
