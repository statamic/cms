@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">
            {{ __('Users') }}
        </h1>
        <a href="{{ cp_route('users.create') }}" class="btn btn-primary">{{ __('Create User') }}</a>
    </div>

    <user-listing></user-listing>

@endsection
