@extends('outside')

@section('title')
    <h1>{{ t('page_not_found') }}</h1>
    <hr>
@endsection

@section('content')

    <p>{{ t('page_not_found_instructions') }}</p>

    <br>

    <div>
        <a class="btn btn-primary btn-block" href="{{ route('cp') }}">{{ t('dashboard') }}</a>
    </div>

@endsection
