@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Two-Factor Authentication'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center scheme-light">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <ui-auth-card
            icon="phone-lock"
            title="{{ __('Set up Two Factor Authentication') }}"
            description="{{ __('statamic::messages.two_factor_account_requirement') }}"
        >
            <enable-two-factor-authentication :routes="{{ json_encode($routes) }}" redirect="{{ $redirect }}" />
        </ui-auth-card>
    </div>
@endsection
