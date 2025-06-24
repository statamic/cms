@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::outside')
@section('title', __('Reset Password'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center pt-20">
        <div class="flex items-center justify-center py-6">
            <x-outside-logo />
        </div>
        <div class="bg-white backdrop-blur-[2px] border border-gray-200 rounded-2xl p-2 shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)]">
            <div class="relative space-y-3 rounded-xl border border-gray-300 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)]">
                <header class="flex flex-col justify-center items-center mb-8 py-3">
                    @if (! old('email'))
                        <ui-button icon="key" class="shadow-ui-xl rounded-xl mb-4" />
                        <ui-heading :level="1" size="xl">
                            {{ __('Reset Your Password') }}
                        </ui-heading>
                        <ui-description :text="__('statamic::messages.forgot_password_enter_email')" class="text-center" />
                    @else
                        <ui-button icon="mail-check" class="shadow-ui-xl rounded-xl mb-4" />
                        <ui-heading :level="1" size="xl">
                            {{ __('Password Reset Sent') }}
                        </ui-heading>
                        <ui-description :text="__('statamic::messages.forgot_password_sent')" class="text-center" />
                    @endif
                </header>

                <form method="POST" action="{{ cp_route('password.email') }}" class="flex flex-col gap-6">
                    @csrf

                    <ui-field :label="__('Email Address')" error="{{ $errors->first('email') }}">
                        <ui-input
                            name="email"
                            value="{{ old('email') }}"
                            autofocus
                            type="email"
                        />
                    </ui-field>

                    <ui-button type="submit" variant="primary" :text="__('Submit')" />
                </form>
            </div>
        </div>
    </div>

    <div class="mt-4 w-full text-center dark:mt-6">
        <a href="{{ cp_route('login') }}" class="text-blue-400 text-sm hover:text-blue-600">
            {{ __('I remember my password') }}
        </a>
    </div>
@endsection
