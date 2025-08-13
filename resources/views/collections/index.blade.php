@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Collections'))

@if($collections->isEmpty())
    @section('content-card-modifiers', 'bg-architectural-lines')
@endif

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
        <header class="py-8 mt-8 text-center">
            <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
                <ui-icon name="collections" class="size-5 text-gray-500"></ui-icon>
                {{ __('Collections') }}
            </h1>
        </header>

        <ui-empty-state-menu heading="{{ __('statamic::messages.collection_configure_intro') }}">
            <ui-empty-state-item
                href="{{ cp_route('collections.create') }}"
                icon="collections"
                heading="{{ __('Create Collection') }}"
                description="{{ __('Get started by creating your first collection.') }}"
            ></ui-empty-state-item>
        </ui-empty-state-menu>
    @endunless

    <x-statamic::docs-callout
        topic="{{ __('Collections') }}"
        url="{{ Statamic::docsUrl('collections') }}"
    />
@endsection
