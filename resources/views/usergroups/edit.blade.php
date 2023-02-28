@extends('statamic::layout')
@section('title', __('Configure User Group'))

@section('content')

    <collection-edit-form>
    </collection-edit-form>

    <user-group-publish-form
        action="{{ cp_route('user-groups.update', $group->handle()) }}"
        method="patch"
        initial-title="{{ $group->title() }}"
        initial-handle="{{ $group->handle() }}"
        :initial-roles="{{ json_encode($roles) }}"
        :creating="false"
        breadcrumb-url="{{ cp_route('user-groups.show', $group->handle()) }}"
        v-cloak
    ></user-group-publish-form>

@endsection
