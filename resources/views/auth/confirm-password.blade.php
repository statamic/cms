@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Confirm Password'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <ui-auth-card
            icon="key"
            title="{{ $method === 'password_confirmation' ? __('Confirm Your Password') : __('Verification Code') }}"
            description="{{ $method === 'password_confirmation'
                ? __('statamic::messages.elevated_session_enter_password')
                : __('statamic::messages.elevated_session_enter_verification_code') }}"
        >
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
        </ui-auth-card>
    </div>
@endsection
