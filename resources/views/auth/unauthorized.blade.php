@extends('statamic::outside')
@section('title', Statamic\trans('Unauthorized'))

@section('content')
@include('statamic::partials.outside-logo')

<div class="card auth-card mx-auto text-center text-gray-700">
    <div class="mb-6">{{ Statamic\trans('Unauthorized') }}</div>

    <a class="btn-primary" href="{{ cp_route('logout') }}?redirect={{ cp_route('login') }}">{{ Statamic\trans('Log out') }}</a>
</div>

@endsection
