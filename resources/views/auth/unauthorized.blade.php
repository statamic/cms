@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::outside')
@section('title', __('Unauthorized'))

@section('content')
    @include('statamic::partials.outside-logo')

    <div class="relative mx-auto flex max-w-xs items-center justify-center rounded shadow-lg">
        <div class="outside-shadow absolute inset-0"></div>
        <div class="card auth-card">
            <div class="mb-6">{{ __('Unauthorized') }}</div>

            @auth
                <a class="btn-primary" href="{{ cp_route('logout') }}?redirect={{ $redirect }}">
                    {{ __('Log out') }}
                </a>
            @else
                <a class="btn-primary" href="{{ cp_route('login') }}">{{ __('Log in') }}</a>
            @endauth
        </div>
    </div>
@endsection
