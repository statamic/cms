@extends('statamic::layout')

@section('content')

    @if(count($roles) == 0)
        <div class="text-center max-w-md mx-auto mt-5 screen-centered border-2 border-dashed rounded-lg px-4 py-8">
            @svg('empty/permission')
            <h1 class="my-3">{{ __('Create your first Role now') }}</h1>
            <p class="text-grey mb-3">
                {{ __('Roles are groups of access and action permissions in the Control Panel that can be assigned to users and user groups.') }}
            </p>
            <a href="{{ cp_route('roles.create') }}" class="btn-primary btn-lg">{{ __('Create Role') }}</a>
        </div>
    @endif

    @if(count($roles) > 0)
        <div class="flex mb-3">
            <h1 class="flex-1">
                {{ __('Roles & Permissions') }}
            </h1>
            <a href="{{ cp_route('roles.create') }}" class="btn btn-primary">{{ __('Create Role') }}</a>
        </div>

        <role-listing
            :initial-rows="{{ json_encode($roles) }}"
            :columns="{{ json_encode($columns) }}">
        </role-listing>
    @endif

@endsection
