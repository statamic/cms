<?php $authed = \Statamic\Facades\User::current(); ?>

@extends($authed ? 'statamic::layout' : 'statamic::outside')

@section('content')
    <div class="min-h-screen flex justify-center {{ $authed ? 'pt-30' : 'items-center' }}">
        <h1 class="text-3xl tracking-tighter mb-10 opacity-50 text-center">{{ __('Page Not Found') }}</h1>
    </div>
@endsection
