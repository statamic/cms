@extends('statamic::layout')
@section('title', __('Roles'))

@section('content')

    @unless($roles->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">
                {{ __('Roles & Permissions') }}
            </h1>
            <a href="{{ cp_route('roles.create') }}" class="btn btn-primary">{{ __('Create Role') }}</a>
        </div>

        <role-listing
            :initial-rows="{{ json_encode($roles) }}"
            :initial-columns="{{ json_encode($columns) }}">
        </role-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Role',
            'description' => 'Roles are groups of access and action permissions in the Control Panel that can be assigned to users and user groups.',
            'svg' => 'empty/permission',
            'route' => cp_route('roles.create')
        ])

    @endunless

@endsection
