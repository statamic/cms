@extends('statamic::layout')

@section('content')

    <user-group-publish-form
        action="{{ cp_route('user-groups.update', $group->handle()) }}"
        method="patch"
        initial-title="{{ $group->title() }}"
        initial-handle="{{ $group->handle() }}"
        :initial-roles="{{ json_encode($roles) }}"
        v-cloak
    >

        <h1 class="flex-1" slot="heading" slot-scope="{ title }">
            <a href="{{ cp_route('user-groups.index') }}">{{ __('User Groups') }}</a>
            @svg('chevron-right')
            @{{ title }}
        </h1>

    </user-group-publish-form>

    <user-listing
        listing-key="usergroup-users"
        group="{{ $group->id() }}"
        :filters="{{ $filters->toJson() }}"
        :actions="{{ $actions->toJson() }}"
        action-url="{{ cp_route('users.action') }}"
    ></user-listing>

@endsection
