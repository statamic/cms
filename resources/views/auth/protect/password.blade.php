@extends('statamic::outside')

@section('content')

    <div class="relative mx-auto max-w-[400px] items-center justify-center">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <ui-auth-card
            icon="key"
            title="{{ __('Protected Page') }}"
            description="{{ !request()->has('token') ? __('statamic::messages.password_protect_token_missing') : __('statamic::messages.password_protect_enter_password') }}"
        >
            <form method="POST" class="flex flex-col gap-6">
                @csrf
                <statamic:protect:password-form class="flex flex-col gap-6">
                    <ui-input
                        type="password"
                        name="password"
                        autofocus
                        placeholder="{{ __('statamic::messages.password_protect_enter_password') }}"
                    />

                    <statamic:get_errors:all bag="passwordProtect">
                        @foreach($messages as $message)
                            <ui-description class="text-red-500" text="{{ $message['message'] }}" />
                        @endforeach
                    </statamic:get_errors:all>

                    <ui-button type="submit" variant="primary" class="w-full">
                        {{ __('Submit') }}
                    </ui-button>
                </statamic:protect:password-form>
            </form>
        </ui-auth-card>
    </div>

@endsection
