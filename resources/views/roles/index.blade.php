@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Roles & Permissions'))

@section('content')
    <ui-header title="{{ __('Roles & Permissions') }}" icon="permissions">
        <ui-button
            href="{{ cp_route('roles.create') }}"
            variant="primary"
            :text="__('Create Role')"
        ></ui-button>
    </ui-header>

    <role-listing
        :initial-rows="{{ json_encode($roles) }}"
        :initial-columns="{{ json_encode($columns) }}"
    ></role-listing>

    <x-statamic::docs-callout
        :topic="__('Roles & Permissions')"
        :url="Statamic::docsUrl('users#permissions')"
    />
@endsection
