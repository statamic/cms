@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Log in'))

@section('content')

    <div class="relative mx-auto max-w-[400px] items-center justify-center pt-20">
        <div class="flex items-center justify-center py-6">
            <x-outside-logo />
        </div>
        <div class="bg-white backdrop-blur-[2px] border border-gray-200 rounded-2xl p-2 shadow-[0_8px_5px_-6px_rgba(0,0,0,0.12),_0_3px_8px_0_rgba(0,0,0,0.02),_0_30px_22px_-22px_rgba(39,39,42,0.35)]">
            <div class="relative space-y-3 rounded-xl border border-gray-300 bg-white p-4 shadow-[0_1px_16px_-2px_rgba(63,63,71,0.2)]">
                <header class="flex flex-col justify-center items-center mb-8 py-3">
                    <ui-button icon="sign-in" class="shadow-ui-xl rounded-xl mb-4" />
                    <ui-heading :level="1" size="xl">
                        {{ $emailLoginEnabled ? __('Sign in with email') : __('Sign in with OAuth') }}
                    </ui-heading>
                    <ui-description :text="__('Sign into your Statamic Control Panel')" />
                </header>
                <login
                    :has-error="{{ $str::bool(count($errors) > 0) }}"
                    v-slot="{ busy, setBusy, hasError }"
                >
                    <div>
                        @if ($emailLoginEnabled)
                            <form method="POST" class="flex flex-col gap-6" @submit="setBusy(true)">
                                {!! csrf_field() !!}

                                <input type="hidden" name="referer" value="{{ $referer }}" />


                                <ui-field :label="__('Email')" error="{{ $errors->first('email') }}">
                                    <ui-input
                                        name="email"
                                        value="{{ old('email') }}"
                                        autofocus
                                    />
                                </ui-field>

                                <ui-field :label="__('Password')" error="{{ $errors->first('password') }}">
                                    <ui-input
                                        name="password"
                                        type="password"
                                        viewable
                                        value="{{ old('password') }}"
                                    />
                                    <template #actions>
                                        <a  href="{{ cp_route('password.request') }}" class="text-blue-400 text-sm hover:text-blue-600">
                                            {{ __('Forgot password?') }}
                                        </a>
                                    </template>
                                </ui-field>

                                <ui-checkbox-item name="remember" :label="__('Remember me')" name="remember"  />

                                <ui-button type="submit" variant="primary" :disabled="busy" :text="__('Continue')" />

                            </form>
                        @endif
                        @if ($oauth)
                            @if ($emailLoginEnabled)
                                <ui-separator variant="dots" :text="__('Or sign in with')" class="py-3" />
                            @endif
                            <div class="flex gap-4 justify-center items-center">
                                @foreach ($providers as $provider)
                                    <ui-button as="href" class="flex-1" href="{{ $provider->loginUrl() }}?redirect={{ parse_url(cp_route('index'))['path'] }}">
                                        @cp_svg("oauth/".$provider->name(), 'size-7')
                                    </ui-button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </login>
            </div>
        </div>
    </div>
@endsection
