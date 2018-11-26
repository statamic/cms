@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">
            {{ __('User Groups') }}
        </h1>
        <a href="{{ cp_route('user-groups.create') }}" class="btn btn-primary">{{ __('Create User Group') }}</a>
    </div>

    <user-group-listing :initial-rows="{{ json_encode($groups) }}"></user-group-listing>

@endsection
