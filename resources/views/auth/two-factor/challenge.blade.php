@php
    use function Statamic\trans as __;
@endphp

@inject("str", "Statamic\Support\Str")
@extends("statamic::outside")
@section("title", __("Two Factor Authentication"))

@section("content")
    @include("statamic::partials.outside-logo")

    <div class="relative mx-auto flex max-w-xs items-center justify-center rounded shadow-lg">
        <div class="outside-shadow absolute inset-0"></div>
        <div class="card auth-card">
            <two-factor-challenge
                initial-mode="{{ $mode }}"
                :has-error="{{ $str::bool(count($errors) > 0) }}"
                v-slot="{ busy, mode, toggleMode, hasError }"
            >
                <form method="POST" action="{{ $action }}" class="email-login select-none" @submit="busy = true">
                    @csrf

                    @if (request("redirect"))
                        <input type="hidden" name="redirect" value="{{ request("redirect") }}" />
                    @endif

                    <h1 class="mb-2 text-lg text-gray-800 dark:text-dark-175">
                        {{ __("Two Factor Authentication") }}
                    </h1>
                    <p
                        v-if="mode === 'code'"
                        @if ($mode === "recovery_code")
                            v-cloak
                        @endif
                        class="mb-4 text-sm text-gray dark:text-dark-175"
                    >
                        {{ __("statamic::messages.two_factor_challenge_code_instructions") }}
                    </p>
                    <p
                        v-if="mode === 'recovery_code'"
                        @if ($mode === "code")
                            v-cloak
                        @endif
                        class="mb-4 text-sm text-gray dark:text-dark-175"
                    >
                        {{ __("statamic::messages.two_factor_recovery_code_instructions") }}
                    </p>

                    <div v-if="mode === 'code'" @if($mode === 'recovery_code') v-cloak @endif class="mb-8">
                        <label class="mb-2" for="input-code">{{ __("Code") }}</label>
                        <input
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
                        @if ($hasError("code"))
                            <div class="mt-2 text-xs text-red-500">{{ $errors->first("code") }}</div>
                        @endif
                    </div>

                    <div v-if="mode === 'recovery_code'" @if($mode === 'code') v-cloak @endif class="mb-8">
                        <label class="mb-2" for="input-recovery-code">{{ __("Recovery Code") }}</label>
                        <input
                            type="text"
                            class="input-text"
                            name="recovery_code"
                            maxlength="21"
                            autofocus
                            autocomplete="off"
                            id="input-recovery-code"
                        />
                        @if ($hasError("recovery_code"))
                            <div class="mt-2 text-xs text-red-500">{{ $errors->first("recovery_code") }}</div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between">
                        <button
                            v-if="mode === 'code'"
                            @if($mode === 'recovery_code') v-cloak @endif
                            class="text-btn text-xs"
                            type="button"
                            @click="toggleMode"
                        >
                            {{ __("Use recovery code") }}
                        </button>

                        <button
                            v-if="mode === 'recovery_code'"
                            @if($mode === 'code') v-cloak @endif
                            class="text-btn text-xs"
                            type="button"
                            @click="toggleMode"
                        >
                            {{ __("Use one-time code") }}
                        </button>

                        <button type="submit" class="btn-primary">{{ __("Continue") }}</button>
                    </div>
                </form>
            </two-factor-challenge>
        </div>
    </div>
@endsection
