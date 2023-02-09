@extends('statamic::outside')
@section('title', __('Unauthorized'))

@section('content')
@include('statamic::partials.outside-logo')

<div class="card auth-card mx-auto text-center text-grey-70">
    <div class="mb-6___REPLACED">{{ __('Unauthorized') }}</div>

    <a class="btn-primary" href="{{ cp_route('logout') }}?redirect={{ cp_route('login') }}">{{ __('Log out') }}</a>
</div>

@endsection
