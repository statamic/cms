@extends('outside')

@section('title')
    <h1>{{ $title }}</h1>
    <hr>
@endsection

@section('content')

    @if (session()->has('success'))

    @elseif (!$code)

        <div class="alert alert-danger">
            <p>{{ t('reset_code_missing') }}</p>
        </div>

    @elseif (! $valid)

        <div class="alert alert-danger">
            <p>{{ t('reset_code_invalid') }}</p>
        </div>

    @else

        <form method="post">
            {!! csrf_field() !!}

            <div class="form-group">
                <label>{{ trans_choice('cp.passwords', 1) }}</label>
                <input type="password" class="form-control" name="password" id="password">
            </div>

            <div class="form-group">
                <label>{{ trans('cp.confirm_password') }}</label>
                <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
            </div>

            <div>
                <button type="submit" class="btn btn-primary btn-block">{{ trans('cp.submit') }}</button>
            </div>

        </form>

    @endif

@endsection
