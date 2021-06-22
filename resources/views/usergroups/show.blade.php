@extends('statamic::layout')
@section('title', Statamic::crumb($group->title(), 'User Group'))
@section('wrapper_class', 'max-w-full')

@section('content')

<header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('user-groups.index'),
            'title' => __('User Groups')
        ])
        <div class="flex items-center">
            <h1 class="flex-1">{{ $group->title() }}</h1>
            <dropdown-list class="mr-1">
                @can('edit', $group)
                    <dropdown-item :text="__('Edit User Group')" redirect="{{ $group->editUrl() }}"></dropdown-item>
                @endcan
                @can('delete', $group)
                    <dropdown-item :text="__('Delete User Group')" class="warning" @click="$refs.deleter.confirm()">
                        <resource-deleter
                            ref="deleter"
                            resource-title="{{ $group->title() }}"
                            route="{{ cp_route('user-groups.destroy', $group->handle()) }}"
                            redirect="{{ cp_route('user-groups.index') }}"
                        ></resource-deleter>
                    </dropdown-item>
                @endcan
            </dropdown-list>
        </div>
    </header>

    <user-listing
        listing-key="usergroup-users"
        group="{{ $group->id() }}"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route('users.actions.run') }}"
    ></user-listing>

@endsection
