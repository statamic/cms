@extends('statamic::outside')

@section('content')
    @include('statamic::partials.outside-logo')

    <div class="card auth-card mx-auto">
        <div class="text-center pb-2 mb-2">
            <h1 class="mb-2 text-lg text-grey-80">{{ __('Forgot Your Password?') }}</h1>
            <p class="text-sm text-grey">{{ __('statamic::messages.forgot_password_enter_email') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success mb-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ cp_route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="mb-1">{{ __('Email Address') }}</label>
                <input id="email" type="text" class="input-text input-text" name="email" value="{{ old('email') }}" >

                @error('email', 'user.forgot_password')
                    <div class="text-red text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                {{ __('Submit') }}
            </button>
        </form>

    </div>

    <div class="w-full text-center mt-2">
        <a href="{{ cp_route('login') }}" class="forgot-password-link text-sm opacity-75 hover:opacity-100">
            {{ __('I remember my password') }}
        </a>
    </div>

@endsection
