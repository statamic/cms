@extends('statamic::outside')

@section('content')

    <h1 class="mb-6___REPLACED pt-20___REPLACED text-center text-grey-80">{{ $title }}</h1>

    <div class="card auth-card mx-auto">

        <form method="POST" action="{{ $action }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            @if (request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif

            <div class="mb-8___REPLACED">
                <label for="email"  class="mb-2___REPLACED">{{ __('Email Address') }}</label>

                <input id="email" type="email" class="input-text input-text" name="email" value="{{ $email ?? old('email') }}" autofocus required>

                @error('email')
                    <div class="text-red text-xs mt-2___REPLACED">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-8___REPLACED">
                <label for="password" class="mb-2___REPLACED">{{ __('Password') }}</label>

                <input id="password" type="password" class="input-text input-text" name="password" required>

                @error('password')
                    <div class="text-red text-xs mt-2___REPLACED">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-8___REPLACED">
                <label for="password-confirm" class="mb-2___REPLACED">{{ __('Confirm Password') }}</label>

                <input id="password-confirm" type="password" class="input-text input-text" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn-primary">{{ $title }}</button>

        </form>

    </div>

@endsection
