@extends('statamic::layout')
@section('title', __('Edit User Group'))

@section('content')

    <collection-edit-form>
    </collection-edit-form>

    <user-group-publish-form
        action="{{ cp_route('user-groups.update', $group->handle()) }}"
        method="patch"
        initial-title="{{ $group->title() }}"
        initial-handle="{{ $group->handle() }}"
        :initial-roles="{{ json_encode($roles) }}"
        v-cloak
    ></user-group-publish-form>

    <user-listing
        listing-key="usergroup-users"
        group="{{ $group->id() }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route('users.actions') }}"
    ></user-listing>

@endsection
