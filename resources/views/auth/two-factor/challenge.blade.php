@extends('statamic::outside')
@section('title', __('Two Factor Authentication'))

@section('content')
    @include('statamic::partials.outside-logo')
    <div class="two-factor">
        <div class="two-factor-challenge">
            <div class="card auth-card mx-auto" x-data="{ mode: '{{ $mode }}', code: '', recovery_code: '' }">
                <div class="mb-2 pb-4 text-center">
                    <h1 class="mb-4 text-lg text-gray-800 dark:text-dark-175">
                        {{ __('Two Factor Authentication') }}
                    </h1>
                    <p
                        @if($mode === 'recovery_code') x-cloak @endif
                        x-show="mode === 'code'"
                        class="text-sm text-gray dark:text-dark-175"
                    >
                        {{ __('statamic::messages.two_factor_challenge_code_introduction') }}
                    </p>
                    <p
                        @if($mode === 'code') x-cloak @endif
                        x-show="mode === 'recovery_code'"
                        class="text-sm text-gray dark:text-dark-175"
                    >
                        {{ __('statamic::messages.two_factor_recovery_code_introduction') }}
                    </p>
                </div>

                <div>
                    <form method="POST">
                        {!! csrf_field() !!}
                        <input type="hidden" name="mode" x-model="mode" />

                        <div class="mb-8" @if($mode === 'recovery_code') x-cloak @endif x-show="mode === 'code'">
                            <label class="mb-2" for="input-code">{{ __('Code') }}</label>
                            <input
                                x-model="code"
                                type="text"
                                class="input-text"
                                name="code"
                                pattern="[0-9]*"
                                maxlength="6"
                                inputmode="numeric"
                                autofocus
                                autocomplete="off"
                                id="input-code"
                            />
                            @error('code')
                                <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-8" @if($mode === 'code') x-cloak @endif x-show="mode === 'recovery_code'">
                            <label class="mb-2" for="input-recovery-code">{{ __('Recovery Code') }}</label>
                            <input
                                x-model="recovery_code"
                                type="text"
                                class="input-text"
                                name="recovery_code"
                                maxlength="21"
                                autofocus
                                autocomplete="off"
                                id="input-recovery-code"
                            />
                            @error('recovery_code')
                                <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <button
                                class="text-btn text-xs"
                                type="button"
                                @if($mode === 'recovery_code') x-cloak @endif
                                x-on:click.prevent="mode = 'recovery_code'; code = ''"
                                x-show="mode === 'code'"
                            >
                                {{ __('Use recovery code') }}
                            </button>

                            <button
                                class="text-btn text-xs"
                                type="button"
                                @if($mode === 'code') x-cloak @endif
                                x-on:click.prevent="mode = 'code'; recovery_code = ''"
                                x-show="mode === 'recovery_code'"
                            >
                                {{ __('Use one-time code') }}
                            </button>

                            <button type="submit" class="btn-primary">{{ __('Continue') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 text-center text-sm">
                <a
                    class="logout opacity-75 hover:opacity-100"
                    href="{{ cp_route('logout') }}?redirect={{ cp_route('login') }}"
                >
                    {{ __('Log out') }}
                </a>
            </div>
        </div>
    </div>
@endsection
