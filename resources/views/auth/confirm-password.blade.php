@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Confirm Password'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center pt-20">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <div class="bg-white backdrop-blur-[2px] border border-gray-200 rounded-2xl p-2 shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)]">
            <div class="relative space-y-3 rounded-xl border border-gray-300 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)]">
                <header class="flex flex-col justify-center items-center mb-8 py-3">
                    <ui-card class="p-2! mb-4 flex items-center justify-center">
                        <ui-icon name="key" class="size-5" />
                    </ui-card>
                    <ui-heading :level="1" size="xl">
                        @if ($method === 'password_confirmation')
                            {{ __('Confirm Your Password') }}
                        @else
                            {{ __('Verification Code') }}
                        @endif
                    </ui-heading>
                    <ui-description
                        class="text-center"
                        :text="$method === 'password_confirmation'
                            ? __('statamic::messages.elevated_session_enter_password')
                            : __('statamic::messages.elevated_session_enter_verification_code')"
                    />
                </header>

                @if (session('status'))
                    <ui-alert variant="success" :text="session('status')" class="mb-6" />
                @endif

                <form method="POST" action="{{ cp_route('elevated-session.confirm') }}" class="flex flex-col gap-6">
                    @csrf

                    @if ($method === 'password_confirmation')
                        <ui-field :label="__('Password')" error="{{ $errors->first('password') }}">
                            <ui-input
                                name="password"
                                type="password"
                                viewable
                            />
                        </ui-field>
                    @endif

                    @if ($method === 'verification_code')
                        <ui-field :label="__('Verification Code')" error="{{ $errors->first('verification_code') }}">
                            <ui-input
                                name="verification_code"
                                autofocus
                            />
                        </ui-field>
                    @endif

                    <div class="flex items-center gap-4">
                        <ui-button type="submit" variant="primary" :text="__('Submit')" class="flex-1"/>

                        @if ($method === 'verification_code')
                            <ui-button
                                as="href"
                                class="flex-1"
                                href="{{ cp_route('elevated-session.resend-code') }}"
                                :text="__('Resend code')"
                            />
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
