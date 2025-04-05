@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('User Groups'))

@section('content')
    @unless ($groups->isEmpty())
        <ui-header title="{{ __('User Groups') }}">
            <ui-button
                href="{{ cp_route('user-groups.create') }}"
                variant="primary"
                :text="__('Create User Group')"
            ></ui-button>
        </ui-header>

        <user-group-listing :initial-rows="{{ json_encode($groups) }}"></user-group-listing>
    @else
        @include(
            'statamic::partials.empty-state',
            [
                'title' => __('User Groups'),
                'description' => __('statamic::messages.user_groups_intro'),
                'svg' => 'empty/users',
                'button_text' => __('Create User Group'),
                'button_url' => cp_route('user-groups.create'),
            ]
        )
    @endunless

    @include(
        'statamic::partials.docs-callout',
        [
            'topic' => __('User Groups'),
            'url' => Statamic::docsUrl('users#user-groups'),
        ]
    )
@endsection
