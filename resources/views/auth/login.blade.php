@extends('statamic::outside')

@section('content')
<div class="logo">
    {!! inline_svg('statamic-wordmark') !!}
</div>
<div class="box card mx-auto">
    <login inline-template :show-email-login="!{{ bool_str($oauth) }}" :has-error="{{ bool_str(count($errors) > 0) }}">

        @if ($oauth)
            <div class="login-oauth-providers">
                @foreach (Statamic\API\OAuth::providers() as $provider => $data)
                    <div class="provider">
                        <a href="{{ Statamic\API\OAuth::route($provider) }}?redirect={{ parse_url(route('cp'))['path'] }}" class="btn btn-block btn-primary">
                            {{ t('login_with', ['provider' => array_get($data, 'label', \Statamic\API\Str::title($provider))]) }}
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="login-or">or</div>

            <div class="login-with-email" v-if="! showEmailLogin">
                <a class="btn btn-block" @click.prevent="showEmailLogin = true">
                    {{ t('login_with', ['provider' => t(\Statamic\API\Config::get('users.login_type'))]) }}
                </a>
            </div>
        @endif

        <form method="POST" v-show="showEmailLogin" class="email-login select-none">
            {!! csrf_field() !!}

            <input type="hidden" name="referer" value="{{ $referer }}" />

            <div class="mb-4">
                <label class="mb-1">
                @if (\Statamic\API\Config::get('users.login_type') === 'email')
                    {{ __('Email') }}
                @else
                    {{ __('Username') }}
                @endif
                </label>
                <input type="text" class="form-control" name="username" value="{{ old('username') }}" autofocus>
            </div>

            <div class="mb-4">
                <label class="mb-1">{{ __('Password') }}</label>
                <input type="password" class="form-control" name="password" id="password">
            </div>
            <div class="flex justify-between items-center">
                <label for="remember_me" class="flex items-center">
                    <input type="checkbox" name="remember" id="remember_me">
                    <span class="ml-1">{{ __('Remember me') }}</span>
                </label>
                <button type="submit" class="btn">{{ __('Login') }}</button>
            </div>
        </form>
    </login>
</div>
@if (! $oauth)
    <div class="w-full text-center mt-2">
        <a href="{{ route('login.reset')}}" class="text-white text-sm text-shadow opacity-75 hover:opacity-100">
            {{ __('Forgot password?') }}
        </a>
    </div>
@endif

@endsection
