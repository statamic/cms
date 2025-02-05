@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::outside')

@section('content')
    <h1 class="mb-6 pt-20 text-center text-gray-800 dark:text-white/80">{{ $title }}</h1>

    <div class="relative mx-auto flex max-w-xs items-center justify-center rounded shadow-lg">
        <div class="outside-shadow absolute inset-0"></div>
        <div class="card auth-card" x-data="{ busy: false }" v-pre>
            <form method="POST" action="{{ $action }}" x-on:submit="busy = true">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}" />

                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}" />
                @endif

                <div class="mb-8">
                    <label for="email" class="mb-2">{{ __('Email Address') }}</label>

                    <input
                        id="email"
                        type="email"
                        class="input-text input-text"
                        name="email"
                        value="{{ $email ?? old('email') }}"
                        autofocus
                        required
                    />

                    @error('email')
                        <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-8">
                    <label for="password" class="mb-2">{{ __('Password') }}</label>

                    <input id="password" type="password" class="input-text input-text" name="password" required />

                    @error('password')
                        <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-8">
                    <label for="password-confirm" class="mb-2">{{ __('Confirm Password') }}</label>

                    <input
                        id="password-confirm"
                        type="password"
                        class="input-text input-text"
                        name="password_confirmation"
                        required
                    />
                </div>

                <button type="submit" class="btn-primary" :disabled="busy">{{ $title }}</button>
            </form>
        </div>
    </div>
@endsection
