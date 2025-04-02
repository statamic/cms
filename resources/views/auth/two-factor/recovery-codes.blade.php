@extends('statamic::outside')
@section('title', __('statamic-two-factor::recovery-codes.title'))

@section('content')

    @include('statamic::partials.outside-logo')
    <div class="two-factor">
        <div class="two-factor-recovery-codes">

            <div class="card auth-card mx-auto">

                <div class="pb-4 mb-2 text-center">
                    <h1 class="mb-4 text-lg text-gray-800 dark:text-dark-175">{{ __('statamic-two-factor::recovery-codes.title') }}</h1>
                    <p class="text-sm text-gray">{{ __('statamic-two-factor::recovery-codes.introduction') }}</p>
                </div>

                <div>
                    <div
                        class="mb-6 bg-gray-300 dark:bg-dark-650 rounded px-2 py-6 space-y-1 text-sm font-mono text-center">
                        @foreach ($recovery_codes as $recovery_code)
                            <div>{{ $recovery_code }}</div>
                        @endforeach
                    </div>


                    <form method="POST" action="{{ cp_route('two-factor.complete') }}">
                        {!! csrf_field() !!}

                        <div class="flex justify-between items-center">
                            <div></div>
                            <button type="submit"
                                    class="btn-primary">{{ __('statamic-two-factor::recovery-codes.action') }}</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

@endsection
