@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::outside')
@section('title', __('Unauthorized'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <ui-auth-card
            icon="key"
            title="{{ __('Unauthorized') }}"
            description="{{ __('You do not have permission to access this URL') }}"
        >
            <div class="flex justify-center">
                @auth
                    <ui-button
                        as="href"
                        variant="primary"
                        href="{{ cp_route('logout') }}?redirect={{ $redirect }}"
                        class="w-full"
                    >
                        {{ __('Log out') }}
                    </ui-button>
                @else
                    <ui-button as="href" variant="primary" href="{{ cp_route('login') }}" class="w-full">
                        {{ __('Log in') }}
                    </ui-button>
                @endauth
            </div>
        </ui-auth-card>
    </div>
@endsection
