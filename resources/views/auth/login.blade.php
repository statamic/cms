@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Log in'))

@section('content')
    @include('statamic::partials.outside-logo')

    <div class="relative mx-auto flex max-w-xs items-center justify-center rounded shadow-lg">
        <div class="outside-shadow absolute inset-0"></div>
        <div class="card auth-card">
            <login
                :show-email-login="!{{ $str::bool($oauth) }}"
                :has-error="{{ $str::bool(count($errors) > 0) }}"
                v-slot="{ showEmailLogin, busy, hasError }"
            >
                <div>
                    @if ($oauth)
                        <div class="login-oauth-providers">
                            @foreach ($providers as $provider)
                                <div class="provider mb-2">
                                    <a
                                        href="{{ $provider->loginUrl() }}?redirect={{ parse_url(cp_route('index'))['path'] }}"
                                        class="btn-primary w-full"
                                    >
                                        {{ __('Log in with :provider', ['provider' => $provider->label()]) }}
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        @if ($emailLoginEnabled)
                            <div class="py-6 text-center text-sm text-gray-700">&mdash; {{ __('or') }} &mdash;</div>

                            <div class="login-with-email" v-if="! showEmailLogin">
                                <a class="btn w-full" @click.prevent="showEmailLogin = true">
                                    {{ __('Log in with email') }}
                                </a>
                            </div>
                        @endif
                    @endif

                    <form
                        method="POST"
                        v-show="showEmailLogin"
                        class="email-login select-none"
                        @if ($oauth) v-cloak @endif
                        @submit="busy = true"
                    >
                        {!! csrf_field() !!}

                        <input type="hidden" name="referer" value="{{ $referer }}" />

                        <div class="mb-8">
                            <label class="mb-2" for="input-email">{{ __('Email') }}</label>
                            <input
                                type="text"
                                class="input-text input-text"
                                name="email"
                                value="{{ old('email') }}"
                                autofocus
                                id="input-email"
                            />
                            @if ($hasError('email'))
                                <div class="mt-2 text-xs text-red-500">{{ $errors->first('email') }}</div>
                            @endif
                        </div>

                        <div class="mb-8">
                            <label class="mb-2" for="input-password">{{ __('Password') }}</label>
                            <input type="password" class="input-text input-text" name="password" id="input-password" />
                            @if ($hasError('password'))
                                <div class="mt-2 text-xs text-red-500">{{ $errors->first('password') }}</div>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <label for="remember-me" class="flex cursor-pointer items-center">
                                <input type="checkbox" name="remember" id="remember-me" />
                                <span class="ltr:ml-2 rtl:mr-2">{{ __('Remember me') }}</span>
                            </label>
                            <button type="submit" class="btn-primary" :disabled="busy">{{ __('Log in') }}</button>
                        </div>
                    </form>
                </div>
            </login>
        </div>
    </div>
    @if ($emailLoginEnabled)
        <div class="mt-4 w-full text-center dark:mt-6">
            <a
                href="{{ cp_route('password.request') }}"
                class="forgot-password-link text-sm opacity-75 hover:opacity-100"
            >
                {{ __('Forgot password?') }}
            </a>
        </div>
    @endif
@endsection
