@extends('statamic::outside')

@section('content')
    @include('statamic::partials.outside-logo')

    <div class="card auth-card mx-auto">
        <div class="text-center pb-4 mb-4">
            <h1 class="mb-4 text-lg text-gray-800">{{ __('Forgot Your Password?') }}</h1>
            <p class="text-sm text-gray">{{ __('statamic::messages.forgot_password_enter_email') }}</p>
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
                <input id="email" type="text" class="input-text input-text" name="email" value="{{ old('email') }}" >

                @error('email', 'user.forgot_password')
                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                {{ __('Submit') }}
            </button>
        </form>

    </div>

    <div class="w-full text-center mt-4">
        <a href="{{ cp_route('login') }}" class="forgot-password-link text-sm opacity-75 hover:opacity-100">
            {{ __('I remember my password') }}
        </a>
    </div>

@endsection
