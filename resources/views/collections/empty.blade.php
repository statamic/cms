@php use function Statamic\trans as __; @endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')

<header class="mb-6">
    <h1 v-pre>{{ __($collection->title()) }}</h1>
</header>

<collection-empty-state
    :collection='@json($collection)'
    :create-label="{{ $collection->createLabel() }}"
    :blueprints="{{ Js::from($blueprints) }}"
    site="{{ $site }}"
    :can-edit="{{ $str::bool($user->can('edit', $collection)) }}"
    :can-create="{{ $str::bool($user->can('create', ['Statamic\Contracts\Entries\Entry', $collection, \Statamic\Facades\Site::get($site)])) }}"
    :can-configure-fields="{{ $str::bool($user->can('configure fields')) }}"
    :can-store="{{ $str::bool($user->can('create', 'Statamic\Contracts\Entries\Collection')) }}"
></collection-empty-state>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Collections'),
        'url' => Statamic::docsUrl('collections')
    ])
@stop
