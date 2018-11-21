@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">
            {{ __('Roles & Permissions') }}
        </h1>
        <a href="{{ cp_route('roles.create') }}" class="btn btn-primary">{{ __('Create Role') }}</a>
    </div>

    <role-listing :initial-rows="{{ json_encode($roles) }}"></role-listing>

@endsection
