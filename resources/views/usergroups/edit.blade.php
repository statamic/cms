@extends('statamic::layout')
@section('title', __('Configure User Group'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('user-groups.show', $group->handle()),
        'title' => $group->title()
    ])

    <h1 class="mb-3">@yield('title')</h1>

    <collection-edit-form>
    </collection-edit-form>

    <user-group-publish-form
        action="{{ cp_route('user-groups.update', $group->handle()) }}"
        method="patch"
        initial-title="{{ $group->title() }}"
        initial-handle="{{ $group->handle() }}"
        :initial-roles="{{ json_encode($roles) }}"
        :creating="false"
        v-cloak
    ></user-group-publish-form>

@endsection
