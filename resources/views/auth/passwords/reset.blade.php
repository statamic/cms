@extends('statamic::outside')

@section('content')

    <h1 class="mb-6 pt-20 text-center text-gray-800">{{ $title }}</h1>

    <div class="card auth-card mx-auto">

        <form method="POST" action="{{ $action }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            @if (request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif

            <div class="mb-8">
                <label for="email"  class="mb-2">{{ __('Email Address') }}</label>

                <input id="email" type="email" class="input-text input-text" name="email" value="{{ $email ?? old('email') }}" autofocus required>

                @error('email')
                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-8">
                <label for="password" class="mb-2">{{ __('Password') }}</label>

                <input id="password" type="password" class="input-text input-text" name="password" required>

                @error('password')
                    <div class="text-red-500 text-xs mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-8">
                <label for="password-confirm" class="mb-2">{{ __('Confirm Password') }}</label>

                <input id="password-confirm" type="password" class="input-text input-text" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn-primary">{{ $title }}</button>

        </form>

    </div>

@endsection
