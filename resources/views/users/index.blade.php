@extends('statamic::layout')

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">
            {{ __('Users') }}
        </h1>

        @can('create', 'Statamic\Contracts\Auth\User')
            <a href="{{ cp_route('users.create') }}" class="btn btn-primary">{{ __('Create User') }}</a>
        @endcan
    </div>

    <user-listing
        :filters="{{ $filters->toJson() }}"
    ></user-listing>

@endsection
