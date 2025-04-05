@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Global Sets'))

@section('content')
    @unless ($globals->isEmpty())
        <ui-header title="{{ __('Globals') }}">
            @can('create', 'Statamic\Contracts\Globals\GlobalSet')
                <ui-button
                    href="{{ cp_route('globals.create') }}"
                    text="{{ __('Create Global Set') }}"
                    variant="primary"
                />
            @endcan
        </ui-header>

        <global-listing
            :globals="{{ json_encode($globals) }}"
        ></global-listing>
    @else
        <x-statamic::empty-screen
            title="{{ __('Globals') }}"
            description="{{ __('statamic::messages.global_set_config_intro') }}"
            svg="empty/globals"
            button_url="{{ cp_route('globals.create') }}"
            button_text="{{ __('Create Global Set') }}"
            can="{{ $user->can('create', 'Statamic\Contracts\Globals\GlobalSet') }}"
        />
    @endunless

    <x-statamic::docs-callout
        topic="{{ __('Global Variables') }}"
        url="{{ Statamic::docsUrl('globals') }}"
    />
@endsection
