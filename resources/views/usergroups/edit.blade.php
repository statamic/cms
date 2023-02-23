@extends('statamic::layout')
@section('title', __('Configure User Group'))

@section('content')

    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('user-groups.show', $group->handle()),
            'title' => __('User Groups')
        ])
        <div class="flex items-center justify-between">
            <h1>{{ $group->title() }}</h1>
            <button type="submit" class="btn-primary">{{ __('Save') }}</button>
        </div>
    </header>

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
