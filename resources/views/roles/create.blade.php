@extends('statamic::layout')
@section('title', Statamic::crumb(__('Create Role'), __('Roles & Permissions')))

@section('content')

    <role-publish-form
        action="{{ cp_route('roles.store') }}"
        method="post"
        :initial-permissions="{{ json_encode($permissions) }}"
        breadcrumb-url="{{ cp_route('roles.index') }}"
        index-url="{{ cp_route('roles.index') }}"
        v-cloak
    ></role-publish-form>

@endsection
