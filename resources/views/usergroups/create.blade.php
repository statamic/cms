@extends('statamic::layout')

@section('content')

    <user-group-publish-form
        action="{{ cp_route('user-groups.store') }}"
        method="post"
        :role-suggestions="{{ json_encode($roleSuggestions) }}"
        :user-suggestions="{{ json_encode($userSuggestions) }}"
        v-cloak
    >

        <h1 class="flex-1" slot="heading" slot-scope="{ title }">
            <a href="{{ cp_route('user-groups.index') }}">{{ __('User Groups') }}</a>
            @svg('chevron-right')
            {{ __('Create User Group') }}
        </h1>

    </user-group-publish-form>

@endsection
