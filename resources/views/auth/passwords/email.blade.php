@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::outside')
@section('title', __('Reset Password'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <ui-auth-card>
            <header class="mb-8 flex flex-col items-center justify-center py-3">
                @if (! old('email'))
                    <ui-card class="mb-4 flex items-center justify-center p-2!">
                        <ui-icon name="key" class="size-5" />
                    </ui-card>
                    <ui-heading :level="1" size="xl">
                        {{ __('Reset Your Password') }}
                    </ui-heading>
                    <ui-description :text="__('statamic::messages.forgot_password_enter_email')" class="text-center" />
                @else
                    <ui-card class="mb-4 flex items-center justify-center p-2!">
                        <ui-icon name="mail-check" class="size-5" />
                    </ui-card>
                    <ui-heading :level="1" size="xl">
                        {{ __('Password Reset Sent') }}
                    </ui-heading>
                    <ui-description :text="__('statamic::messages.forgot_password_sent')" class="text-center" />
                @endif
            </header>

            <form method="POST" action="{{ cp_route('password.email') }}" class="flex flex-col gap-6">
                @csrf

                <ui-field :label="__('Email Address')" error="{{ $errors->first('email') }}">
                    <ui-input name="email" value="{{ old('email') }}" autofocus type="email" />
                </ui-field>

                <ui-button type="submit" variant="primary" :text="__('Submit')" />
            </form>
        </ui-auth-card>
    </div>

    <div class="mt-4 w-full text-center dark:mt-6">
        <a href="{{ cp_route('login') }}" class="text-sm text-blue-400 hover:text-blue-600">
            {{ __('I remember my password') }}
        </a>
    </div>
@endsection
