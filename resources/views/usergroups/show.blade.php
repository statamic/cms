@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($group->title(), 'User Group'))

@section('content')
    <ui-header title="{{ __($group->title()) }}" icon="groups">
        @can('delete', $group)
            <ui-button @click="$refs.deleter.confirm()">
                {{ __('Delete Group') }}
                <resource-deleter
                    ref="deleter"
                    resource-title="{{ $group->title() }}"
                    route="{{ cp_route('user-groups.destroy', $group->handle()) }}"
                    redirect="{{ cp_route('user-groups.index') }}"
                ></resource-deleter>
            </ui-button>
        @endcan

        @can('edit', $group)
            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                text="{{ __('Edit Group') }}"
                url="{{ $group->editUrl() }}"
                icon="groups"
                prioritize
                v-slot="{ text, url }"
            >
                <ui-button
                    :text="text"
                    :href="url"
                    variant="primary"
                ></ui-button>
            </ui-command-palette-item>
        @endcan
    </ui-header>

    <user-listing
        listing-key="usergroup-users"
        group="{{ $group->id() }}"
        :filters="{{ $filters->toJson() }}"
        :allow-filter-presets="false"
        action-url="{{ cp_route('users.actions.run') }}"
    ></user-listing>
@endsection
