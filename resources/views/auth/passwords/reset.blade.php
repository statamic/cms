@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::outside')
@section('title', __('Set New Password'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <ui-auth-card
            icon="key"
            title="{{ __('Set New Password') }}"
            :description="__('statamic::messages.set_new_password_instructions')"
        >
            <form method="POST" action="{{ $action }}" class="flex flex-col gap-6">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}" />

                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}" />
                @endif

                <ui-field :label="__('Email Address')" error="{{ $errors->first('email') }}">
                    <ui-input name="email" value="{{ $email ?? old('email') }}" autofocus type="email" />
                </ui-field>

                <ui-field :label="__('Password')" error="{{ $errors->first('password') }}">
                    <ui-input name="password" type="password" />
                </ui-field>

                <ui-field :label="__('Confirm Password')" error="{{ $errors->first('password_confirmation') }}">
                    <ui-input name="password_confirmation" type="password" />
                </ui-field>

                <ui-button type="submit" variant="primary" :text="$title" />
            </form>
        </ui-auth-card>
    </div>

    <div class="mt-4 w-full text-center dark:mt-6">
        <a href="{{ cp_route('login') }}" class="text-sm text-blue-400 hover:text-blue-600">
            {{ __('Back to login') }}
        </a>
    </div>
@endsection
