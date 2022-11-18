@extends('statamic::layout')
@section('title', Statamic::crumb(__('Configure Role'), __('Permissions')))

@section('content')

    <role-publish-form
        action="{{ cp_route('roles.update', $role->handle()) }}"
        method="patch"
        :can-assign-super="{{ Statamic\Support\Str::bool($user->isSuper()) }}"
        initial-title="{{ $role->title() }}"
        initial-handle="{{ $role->handle() }}"
        :initial-super="{{ Statamic\Support\Str::bool($super) }}"
        :initial-permissions="{{ json_encode($permissions) }}"
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
