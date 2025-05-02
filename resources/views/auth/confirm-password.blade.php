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
                @if ($method === 'password_confirmation')
                    <h1 class="mb-4 text-lg text-gray-800 dark:text-white/80">{{ __('Confirm Your Password') }}</h1>
                    <p class="text-sm text-gray dark:text-dark-175">
                        {{ __('statamic::messages.elevated_session_enter_password') }}
                    </p>
                @else
                    <h1 class="mb-4 text-lg text-gray-800 dark:text-white/80">{{ __('Verification Code') }}</h1>
                    <p class="text-sm text-gray dark:text-dark-175">
                        {{ __('statamic::messages.elevated_session_enter_verification_code') }}
                    </p>
                @endif
            </div>

            @if (session('status'))
                <div class="alert alert-success mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ cp_route('elevated-session.confirm') }}">
                @csrf

                @if ($method === 'password_confirmation')
                    <div class="mb-8">
                        <label for="password" class="mb-2">{{ __('Password') }}</label>
                        <input id="password" type="password" class="input-text" name="password" />

                        @error('password')
                            <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                @if ($method === 'verification_code')
                    <div class="mb-8">
                        <label for="verification_code" class="mb-2">{{ __('Verification Code') }}</label>
                        <input id="verification_code" type="text" class="input-text" name="verification_code" />

                        @error('verification_code')
                            <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <button type="submit" class="btn-primary">
                    {{ __('Submit') }}
                </button>

                @if ($method === 'verification_code')
                    <a href="{{ cp_route('elevated-session.resend-code') }}" class="ml-4 text-sm text-gray-700">
                        {{ __('Resend code') }}
                    </a>
                @endif
            </form>
        </div>
    </div>
@endsection
