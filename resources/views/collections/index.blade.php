@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Collections'))

@section('content')
    @unless ($collections->isEmpty())
        <collection-list
            :initial-rows="{{ json_encode($collections) }}"
            :initial-columns="{{ json_encode($columns) }}"
            :can-create-collections="{{ $user->can('create', 'Statamic\Contracts\Entries\Collection') ? 'true' : 'false' }}"
            create-url="{{ cp_route('collections.create') }}"
            action-url="{{ cp_route('collections.actions.run') }}"
        ></collection-list>
    @else
        <x-statamic::empty-screen
            title="{{ __('Collections') }}"
            description="{{ __('statamic::messages.collection_configure_intro') }}"
            svg="empty/content"
            button_text="{{ __('Create Collection') }}"
            button_url="{{ cp_route('collections.create') }}"
            can="{{ $user->can('create', 'Statamic\Contracts\Entries\Collection') }}"
        />
    @endunless

    <x-statamic::docs-callout topic="{{ __('Collections') }}" url="{{ Statamic::docsUrl('collections') }}" />
@endsection
