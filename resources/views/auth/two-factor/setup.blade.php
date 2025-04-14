@extends('statamic::outside')
@section('title', __('Set up Two Factor Authentication'))

@section('content')
    @include('statamic::partials.outside-logo')
    <div class="two-factor">
        <div class="two-factor-setup">
            <div class="card auth-card mx-auto">
                <div>
                    <h1 class="mb-4 text-lg text-gray-800 dark:text-dark-175">
                        {{ __('Set up Two Factor Authentication') }}
                    </h1>
                </div>
                <form method="POST" class="">
                    {!! csrf_field() !!}

                    <div class="md:flex">
                        <div class="left">
                            <div class="mb-4 bg-white p-2" style="width: 170px; flex-shrink: 0">
                                {!! $qr !!}
                            </div>

                            <div class="text-sm text-gray-800 dark:text-dark-175">
                                <div class="text-xs font-bold">{{ __('Code') }}:</div>
                                <div>{{ $secret_key }}</div>
                            </div>
                        </div>

                        <div class="right">
                            <div class="mb-6">
                                <p class="text-sm text-gray dark:text-dark-175">
                                    {{ __('statamic::messages.two_factor_setup_instructions') }}
                                </p>
                            </div>

                            <div class="md:hidden">
                                <div class="mb-2 bg-white p-2" style="width: 100%">
                                    {!! $qr !!}
                                </div>

                                <div class="mb-6 text-sm">
                                    <div class="text-xs font-bold">{{ __('Code') }}:</div>
                                    <div>{{ $secret_key }}</div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="mb-2" for="input-code">{{ __('Verification Code') }}</label>
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
                                @error('code')
                                    <div class="mt-2 text-xs text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="flex items-center justify-between">
                                <div></div>

                                <div class="flex space-x-2">
                                    <button type="submit" class="btn-primary">{{ __('Enable 2FA') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-4 text-center text-sm">
                <a
                    class="logout opacity-75 hover:opacity-100"
                    href="{{ cp_route('logout') }}?redirect={{ cp_route('login') }}"
                >
                    {{ __('Log out') }}
                </a>
            </div>
        </div>
    </div>
@endsection
