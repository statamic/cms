@extends('statamic::outside')

@section('title')
    <h1>{{ t('permission_denied') }}</h1>
    <hr>
@endsection

@section('content')

    <p>{{ t('permission_denied_instructions') }}</p>

    <br>

    <div>
        @if (Auth::check())
            <a class="btn btn-primary btn-block" href="{{ route('logout') }}">{{ t('logout') }}</a>
        @else
            <a class="btn btn-primary btn-block" href="{{ route('login') }}">{{ t('login') }}</a>
        @endif
    </div>

@endsection
