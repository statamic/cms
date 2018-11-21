@extends('statamic::layout')

@section('content')

    <role-publish-form
        action="{{ cp_route('roles.store') }}"
        method="post"
        :initial-permissions="{{ json_encode($permissions) }}"
        v-cloak
    >

        <h1 class="flex-1" slot="heading" slot-scope="{ title }">
            <a href="{{ cp_route('roles.index') }}">{{ __('Roles & Permissions') }}</a>
            @svg('chevron-right')
            {{ __('Create Role') }}
        </h1>

    </role-publish-form>

@endsection
