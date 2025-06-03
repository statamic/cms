@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb(__('Configure Role'), __('Permissions')))

@section('content')
    <role-publish-form
        action="{{ cp_route('roles.update', $role->handle()) }}"
        method="patch"
        :can-assign-super="{{ Statamic\Support\Str::bool($user->isSuper()) }}"
        initial-title="{{ $role->title() }}"
        initial-handle="{{ $role->handle() }}"
        :initial-super="{{ Statamic\Support\Str::bool($super) }}"
        :initial-permissions="{{ json_encode($permissions) }}"
        breadcrumb-url="{{ cp_route('roles.index') }}"
        v-cloak
    ></role-publish-form>
@endsection
