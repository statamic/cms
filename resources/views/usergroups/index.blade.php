@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('User Groups'))

@section('content')
    <ui-header title="{{ __('User Groups') }}" icon="groups">
        <ui-button
            href="{{ cp_route('user-groups.create') }}"
            variant="primary"
            :text="__('Create User Group')"
        ></ui-button>
    </ui-header>

    <user-group-listing :initial-rows="{{ json_encode($groups) }}"></user-group-listing>

    <x-statamic::docs-callout :topic="__('User Groups')" :url="Statamic::docsUrl('users#user-groups')" />
@endsection
