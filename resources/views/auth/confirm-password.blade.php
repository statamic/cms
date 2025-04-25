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
                <h1 class="mb-4 text-lg text-gray-800 dark:text-white/80">{{ __('Confirm Your Password') }}</h1>
                <p class="text-sm text-gray dark:text-dark-175">
                    {{ __('statamic::messages.elevated_session_enter_password') }}
                </p>
            </div>

            @if (session('status'))
                <div class="alert alert-success mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ cp_route('elevated-session.confirm') }}">
                @csrf

                <div class="mb-8">
                    <label for="password" class="mb-2">{{ __('Password') }}</label>
                    <input id="password" type="password" class="input-text" name="password" />

                    @error('password')
                        <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">
                    {{ __('Submit') }}
                </button>
            </form>
        </div>
    </div>
@endsection
