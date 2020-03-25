@extends('statamic::layout')
@section('title', __('Create User Group'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('user-groups.index'),
        'title' => __('User Groups')
    ])

    <h1 class="mb-3">{{ __('Create User Group') }}</h1>

    <user-group-publish-form
        action="{{ cp_route('user-groups.store') }}"
        method="post"
        :creating="true"
        v-cloak
    ></user-group-publish-form>

@endsection
