@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">{{ __('Addons') }}</h1>
        <a href="" class="btn mr-2">{{ __('Connect to Your Account') }}</a>
        <a href="" class="btn">{{ __('Refresh List') }}</a>
    </div>

    <addon-list
        domain="{{ env('STATAMIC_DOMAIN') ?? 'https://statamic.com' }}"
        :endpoints="{'addons': '/addons'}">
    </addon-list>

@endsection
