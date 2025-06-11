@php
    use function Statamic\trans as __;
    $collectionData = [
        'handle' => $collection->handle(),
        'createLabel' => $collection->createLabel(),
        'entryBlueprints' => $collection->entryBlueprints()->map(function($blueprint) {
            return [
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title()
            ];
        })->values()->all()
    ];
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')

<header class="mb-6">
    <h1 v-pre>{{ __($collection->title()) }}</h1>
</header>

<collection-empty-state
    :collection='@json($collectionData)'
    :site="$site"
    :can-edit="@can('edit', $collection) true @else false @endcan"
    :can-create="@can('create', ['Statamic\Contracts\Entries\Entry', $collection, \Statamic\Facades\Site::get($site)]) true @else false @endcan"
    :can-configure-fields="@can('configure fields') true @else false @endcan"
    :can-store="@can('store', 'Statamic\Contracts\Entries\Collection') true @else false @endcan"
></collection-empty-state>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Collections'),
        'url' => Statamic::docsUrl('collections')
    ])
@stop
