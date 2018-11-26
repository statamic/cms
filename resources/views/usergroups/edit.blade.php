@extends('statamic::layout')

@section('content')

    <user-group-publish-form
        action="{{ cp_route('user-groups.update', $group->handle()) }}"
        method="patch"
        initial-title="{{ $group->title() }}"
        initial-handle="{{ $group->handle() }}"
        :initial-roles="{{ json_encode($roles) }}"
        :initial-users="{{ json_encode($users) }}"
        :role-suggestions="{{ json_encode($roleSuggestions) }}"
        :user-suggestions="{{ json_encode($userSuggestions) }}"
        v-cloak
    >

        <h1 class="flex-1" slot="heading" slot-scope="{ title }">
            <a href="{{ cp_route('user-groups.index') }}">{{ __('User Groups') }}</a>
            @svg('chevron-right')
            @{{ title }}
        </h1>

    </user-group-publish-form>

@endsection
