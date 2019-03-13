@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Addons') }}</h1>
        <a href="" class="btn">{{ __('Refresh') }}</a>
        <a href="" class="btn-primary ml-2">{{ __('Connect to Your Account') }}</a>
    </div>

    <addon-list></addon-list>

@endsection
