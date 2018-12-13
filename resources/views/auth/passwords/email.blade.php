@extends('statamic::outside')
@section('body_class', 'rad-mode')

@section('content')

    <h1 class="mb-3 pt-7 text-center text-grey-dark">{{ __('Reset Password') }}</h1>

    <div class="card auth-card mx-auto">

        @if (session('status'))
            <div class="alert alert-success mb-3">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ cp_route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="mb-1">{{ __('Email Address') }}</label>
                @if ($errors->has('email'))
                    <small class="block text-red -mt-1 mb-1">{{ $errors->first('email') }}</small>
                @endif
                <input id="email" type="text" class="input-text form-control" name="email" value="{{ old('email') }}" >
            </div>

            <button type="submit" class="btn btn-primary">
                {{ __('Send Password Reset Link') }}
            </button>
        </form>

    </div>

@endsection
