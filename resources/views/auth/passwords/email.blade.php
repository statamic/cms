@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::outside')

@section('content')
    @include('statamic::partials.outside-logo')

    <div class="relative mx-auto flex max-w-xs items-center justify-center rounded shadow-lg">
        <div class="outside-shadow absolute inset-0"></div>
        <div class="card auth-card">
            <div class="mb-4 pb-4 text-center">
                <h1 class="mb-4 text-lg text-gray-800 dark:text-white/80">{{ __('Forgot Your Password?') }}</h1>
                <p class="text-sm text-gray dark:text-dark-175">
                    {{ __('statamic::messages.forgot_password_enter_email') }}
                </p>
            </div>

            @if (session('status'))
                <div class="alert alert-success mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ cp_route('password.email') }}">
                @csrf

                <div class="mb-8">
                    <label for="email" class="mb-2">{{ __('Email Address') }}</label>
                    <input
                        id="email"
                        type="text"
                        class="input-text input-text"
                        name="email"
                        value="{{ old('email') }}"
                    />

                    @error('email', 'user.forgot_password')
                        <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">
                    {{ __('Submit') }}
                </button>
            </form>
        </div>
    </div>

    <div class="mt-4 w-full text-center dark:mt-6">
        <a href="{{ cp_route('login') }}" class="forgot-password-link text-sm opacity-75 hover:opacity-100">
            {{ __('I remember my password') }}
        </a>
    </div>
@endsection
