@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Two-Factor Authentication'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center pt-20 scheme-light">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <div class="bg-white backdrop-blur-[2px] border border-gray-200 rounded-2xl p-2 shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)]">
            <div class="relative space-y-3 rounded-xl border border-gray-300 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)]">
                <header class="flex flex-col justify-center items-center mb-8 py-3">
                    <ui-card class="p-2! mb-4 flex items-center justify-center">
                        <ui-icon name="phone-lock" class="size-5" />
                    </ui-card>
                    <ui-heading :level="1" size="xl">
                        {{ __('Set up Two Factor Authentication') }}
                    </ui-heading>
                    <ui-description :text="__('statamic::messages.two_factor_account_requirement')" class="text-center" />
                </header>

                <enable-two-factor-authentication
                    :routes="{{ json_encode($routes) }}"
                    redirect="{{ $redirect }}"
                />
            </div>
        </div>
    </div>
@endsection
