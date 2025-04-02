@extends('statamic::outside')
@section('title', __('Account is locked'))

@section('content')

    @include('statamic::partials.outside-logo')
    <div class="two-factor">
        <div class="two-factor-locked">

            <div class="card auth-card mx-auto">

                <div class="pb-4 mb-2 text-center">
                    <h1 class="mb-4 text-lg text-gray-800 dark:text-dark-175">{{ __('Account is locked') }}</h1>
                    <p class="text-sm text-gray">{{ __('statamic::messages.two_factor_locked_introduction') }}</p>
                </div>

                <div>
                    <div class="flex justify-center items-center">
                        <a href="{{ cp_route('login') }}"
                           class="btn-primary">{{ __('Return to log in') }}</a>
                    </div>
                </div>

            </div>

        </div>
    </div>

@endsection
