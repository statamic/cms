@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Updater') }}</h1>
        <a href="" class="btn">{{ __('Update to Latest') }}</a>
    </div>

    <updater :ajax-timeout="{{ config('statamic.system.ajax_timeout') }}"></updater>

@endsection
