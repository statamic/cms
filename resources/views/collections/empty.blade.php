@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
            <ui-icon name="collections" class="size-5 text-gray-500"></ui-icon>
            <span v-pre>{{ $collection->title() }}</span>
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('Start designing your collection with these steps') }}">
        @can('edit', $collection)
            <ui-empty-state-item
                href="{{ $collection->editUrl() }}"
                icon="configure"
                heading="{{ __('Configure Collection') }}"
                description="{{ __('statamic::messages.collection_next_steps_configure_description') }}"
            ></ui-empty-state-item>
        @endcan

        @can('create', ['Statamic\Contracts\Entries\Entry', $collection, \Statamic\Facades\Site::get($site)])
            @php($multipleBlueprints = $collection->entryBlueprints()->count() > 1)

            @if($multipleBlueprints)
                <ui-empty-state-item
                    icon="fieldtype-entries"
                    heading="{{ __($collection->createLabel()) }}"
                    description="{{ __('statamic::messages.collection_next_steps_create_entry_description') }}"
                >
                    @foreach ($collection->entryBlueprints() as $blueprint)
                        <a href="{{ cp_route('collections.entries.create', [$collection->handle(), $site, 'blueprint' => $blueprint->handle()]) }}" class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">
                            {{ $blueprint->title() }}
                        </a>
                    @endforeach
                </ui-empty-state-item>
            @else
                <ui-empty-state-item
                    href="{{ cp_route('collections.entries.create', [$collection->handle(), $site]) }}"
                    icon="fieldtype-entries"
                    heading="{{ __($collection->createLabel()) }}"
                    description="{{ __('statamic::messages.collection_next_steps_create_entry_description') }}"
                ></ui-empty-state-item>
            @endif
        @endcan

        @can('configure fields')
            <ui-empty-state-item
                href="{{ cp_route('blueprints.collections.index', [$collection->handle()]) }}"
                icon="blueprints"
                heading="{{ __('Configure Blueprints') }}"
                description="{{ __('statamic::messages.collection_next_steps_blueprints_description') }}"
            ></ui-empty-state-item>
        @endcan

        @can('store', 'Statamic\Contracts\Entries\Collection')
            <ui-empty-state-item
                href="{{ cp_route('collections.scaffold', $collection->handle()) }}"
                icon="scaffold"
                heading="{{ __('Scaffold Views') }}"
                description="{{ __('statamic::messages.collection_next_steps_scaffold_description') }}"
            ></ui-empty-state-item>
        @endcan
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        :topic="__('Collections')"
        :url="Statamic::docsUrl('collections')"
    />
@stop
