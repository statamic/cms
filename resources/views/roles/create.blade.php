@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb(__('Create Role'), __('Roles & Permissions')))

@section('content')
    <role-publish-form
        action="{{ cp_route('roles.store') }}"
        method="post"
        :can-assign-super="{{ Statamic\Support\Str::bool($user->isSuper()) }}"
        :initial-permissions="{{ json_encode($permissions) }}"
        index-url="{{ cp_route('roles.index') }}"
    ></role-publish-form>
@endsection
