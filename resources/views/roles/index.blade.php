@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Roles'))

@section('content')
    @unless ($roles->isEmpty())
        <ui-header title="{{ __('Roles & Permissions') }}">
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
    @else
        <x-statamic::empty-screen
            :title="__('Roles & Permissions')"
            :description="__('statamic::messages.role_intro')"
            :svg="'empty/users'"
            :button-text="__('Create Role')"
            :button-url="cp_route('roles.create')"
        />
    @endunless

    <x-statamic::docs-callout
        :topic="__('Roles & Permissions')"
        :url="Statamic::docsUrl('users#permissions')"
    />
@endsection
