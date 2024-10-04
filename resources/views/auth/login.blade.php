@php use function Statamic\trans as __; @endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Log in'))

@section('content')

@include('statamic::partials.outside-logo')

<div class="max-w-xs rounded shadow-lg flex items-center justify-center relative mx-auto">
    <div class="outside-shadow absolute inset-0"></div>
    <div class="card auth-card">
        <login
            inline-template
            :show-email-login="!{{ $str::bool($oauth) }}"
            :has-error="{{ $str::bool(count($errors) > 0) }}"
            :web-authn-routes=@json($webauthnRoutes)
        >
        <div>
            <form method="POST" v-show="showEmailLogin" class="email-login select-none" @if ($oauth) v-cloak @endif @submit="busy = true">
                {!! csrf_field() !!}

                <input type="hidden" name="referer" value="{{ $referer }}" />

                @if ($emailLoginEnabled)
                    <div class="mb-8">
                        <label class="mb-2" for="input-email">{{ __('Email') }}</label>
                        <input type="text" class="input-text input-text" name="email" value="{{ old('email') }}" autofocus id="input-email">
                        @if ($hasError('email'))<div class="text-red-500 text-xs mt-2">{{ $errors->first('email') }}</div>@endif
                    </div>

                    <div class="mb-8">
                        <label class="mb-2" for="input-password">{{ __('Password') }}</label>
                        <input type="password" class="input-text input-text" name="password" id="input-password">
                        @if ($hasError('password'))<div class="text-red-500 text-xs mt-2">{{ $errors->first('password') }}</div>@endif
                    </div>
                    <div class="flex justify-between items-center">
                        <label for="remember-me" class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" id="remember-me">
                            <span class="rtl:mr-2 ltr:ml-2">{{ __('Remember me') }}</span>
                        </label>
                        <button type="submit" class="btn-primary" :disabled="busy">{{ __('Log in') }}</button>
                    </div>
                @endif

                 @if ($webAuthnEnabled || $oauth)
                    @if ($emailLoginEnabled)
                        <div class="text-center text-sm text-gray-700 py-6" v-show="showWebAuthn || {{ $oauth ? 'true' : 'false' }}">&mdash; {{ __('or') }} &mdash;</div>
                    @endif

                    <div class="flex flex-col gap-2">

                        @if ($webAuthnEnabled)
                            <div class="provider" v-show="showWebAuthn">
                                <button class="w-full btn-flat" type="button" @click="webAuthn()">{{ __('Log in with Passkey') }}</button>
                                <div class="text-red-500 text-xs mt-2 text-center" v-if="showWebAuthnError" v-text="webAuthnError"></div>
                            </div>
                        @endif

                        @if ($oauth)
                            @foreach ($providers as $provider)
                                <div class="provider">
                                    <a href="{{ $provider->loginUrl() }}?redirect={{ parse_url(cp_route('index'))['path'] }}" class="w-full btn-flat">
                                        {{ __('Log in with :provider', ['provider' => $provider->label()]) }}
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

            </form>
        </div>
        </login>
    </div>
</div>
@if ($emailLoginEnabled)
    <div class="w-full text-center mt-4 dark:mt-6">
        <a href="{{ cp_route('password.request') }}" class="forgot-password-link text-sm opacity-75 hover:opacity-100">
            {{ __('Forgot password?') }}
        </a>
    </div>
@endif

@endsection
