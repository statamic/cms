@extends('statamic::layout')
@section('title', __('Create User Group'))

@section('content')

    <user-group-publish-form
        action="{{ cp_route('user-groups.store') }}"
        method="post"
        v-cloak
    >

        <h1 class="flex-1" slot="heading" slot-scope="{ title }">
            <div class="subhead">
                <a href="{{ cp_route('user-groups.index') }}">{{ __('User Groups') }}</a>
            </div>
            {{ __('Create User Group') }}
        </h1>

    </user-group-publish-form>

@endsection
