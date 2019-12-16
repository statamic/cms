@extends('statamic::layout')
@section('title', Statamic::crumb(__('Create Role'), __('Roles & Permissions')))

@section('content')

    <role-publish-form
        action="{{ cp_route('roles.store') }}"
        method="post"
        :initial-permissions="{{ json_encode($permissions) }}"
        v-cloak
    >

        <h1 class="flex-1" slot="heading" slot-scope="{ title }">
            <div class="subhead">
                <a href="{{ cp_route('roles.index') }}">{{ __('Roles & Permissions') }}</a>
            </div>
            {{ __('Create Role') }}
        </h1>

    </role-publish-form>

@endsection
