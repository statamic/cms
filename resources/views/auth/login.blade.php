@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Log in'))

@section('content')

    <div class="relative mx-auto max-w-[400px] items-center justify-center">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <ui-auth-card
            icon="sign-in"
            title="{{ $emailLoginEnabled ? __('Sign in with email') : __('Sign in with OAuth') }}"
            :description="__('Sign into your Statamic Control Panel')"
        >
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
        </ui-auth-card>
    </div>
@endsection
