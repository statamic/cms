@php use function Statamic\trans as __; @endphp

@extends('statamic::outside')
@section('title', __('Unauthorized'))

@section('content')
@include('statamic::partials.outside-logo')

<div class="card auth-card mx-auto text-center text-gray-700">
    <div class="mb-6">{{ __('Unauthorized') }}</div>

    <a class="btn-primary" href="{{ cp_route('logout') }}?redirect={{ cp_route('login') }}">{{ __('Log out') }}</a>
</div>

@endsection
