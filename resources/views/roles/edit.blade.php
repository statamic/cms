@extends('statamic::layout')
@section('title', Statamic::crumb(__('Configure Role'), __('Permissions')))

@section('content')

    <role-publish-form
        :actions="{{ json_encode($actions) }}"
        method="patch"
        initial-title="{{ $role->title() }}"
        initial-handle="{{ $role->handle() }}"
        :initial-super="{{ Statamic\Support\Str::bool($super) }}"
        :initial-permissions="{{ json_encode($permissions) }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :can-edit-blueprint="{{ Statamic\Support\Str::bool($user->can('configure fields')) }}"
        publish-container="base"
        breadcrumb-url="{{ cp_route('roles.index') }}"
        v-cloak
    >

        <h1 class="flex-1" slot="heading" slot-scope="{ title }">
            <div class="subhead">
                <a href="{{ cp_route('roles.index') }}">{{ __('Roles & Permissions') }}</a>
            </div>
            @{{ title }}
        </h1>

    </role-publish-form>

@endsection
