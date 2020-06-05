@extends('statamic::layout')
@section('title', __('Addons'))

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Addons') }}</h1>
    </div>

    <addon-list></addon-list>

@endsection
