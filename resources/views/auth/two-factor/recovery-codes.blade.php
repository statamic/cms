@extends('statamic::outside')
@section('title', __('Recovery Codes'))

@section('content')
    @include('statamic::partials.outside-logo')
    <div class="two-factor">
        <div class="two-factor-recovery-codes">
            <div class="card auth-card mx-auto">
                <div class="mb-2 pb-4 text-center">
                    <h1 class="mb-4 text-lg text-gray-800 dark:text-dark-175">{{ __('Recovery Codes') }}</h1>
                    <p class="text-sm text-gray">{{ __('statamic::messages.two_factor_recovery_codes') }}</p>
                </div>

                <div>
                    <div
                        class="mb-6 space-y-1 rounded bg-gray-300 px-2 py-6 text-center font-mono text-sm dark:bg-dark-650"
                    >
                        @foreach ($recovery_codes as $recovery_code)
                            <div>{{ $recovery_code }}</div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ cp_route('two-factor.complete') }}">
                        {!! csrf_field() !!}

                        <div class="flex items-center justify-between">
                            <div></div>
                            <button type="submit" class="btn-primary">{{ __('Continue') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
