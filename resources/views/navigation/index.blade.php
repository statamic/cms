@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Navigation'))

@section('content')
    @unless ($navs->isEmpty())
        <ui-header title="{{  __('Navigation') }}">
            @can('create', 'Statamic\Contracts\Structures\Nav')
                <ui-button
                    href="{{ cp_route('navigation.create') }}"
                    text="{{ __('Create Navigation') }}"
                    variant="primary"
                />
            @endcan
        </ui-header>

        <navigation-listing
            :initial-rows="{{ json_encode($navs) }}"
        ></navigation-listing>
    @else
        <x-statamic::empty-screen
            title="{{ __('Navigation') }}"
            description="{{ __('statamic::messages.navigation_configure_intro') }}"
            svg="empty/navigation"
            button_text="{{ __('Create Navigation') }}"
            button_url="{{ cp_route('navigation.create') }}"
            can="{{ $user->can('create', 'Statamic\Contracts\Structures\Nav') }}"
        />
    @endunless

    <x-statamic::docs-callout
        topic="{{ __('Navigation') }}"
        url="{{ Statamic::docsUrl('navigation') }}"
    />
@endsection
