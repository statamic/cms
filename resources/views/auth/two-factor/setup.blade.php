@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Set up Two Factor Authentication'))

@section('content')
    @include('statamic::partials.outside-logo')

    <div class="relative mx-auto flex max-w-xs items-center justify-center rounded shadow-lg">
        <div class="outside-shadow absolute inset-0"></div>
        <div class="card auth-card">
            <div>
                <div class="email-login select-none">
                    <h1 class="mb-2 text-lg text-gray-800 dark:text-dark-175">
                        {{ __('Set up Two Factor Authentication') }}
                    </h1>
                    <p class="mb-4 text-sm text-gray dark:text-dark-175">
                        {{ __('Your account requires two factor authentication. Please enable it before proceeding.') }}
                    </p>

                    <enable-two-factor-authentication
                        :routes="{{ json_encode($routes) }}"
                        redirect="{{ $redirect }}"
                    />
                </div>
            </div>
        </div>
    </div>
@endsection
