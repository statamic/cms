@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Assets', $container['title']))
@section('wrapper_class', 'max-w-full')

@section('content')
    <asset-manager
        :initial-container="{{ json_encode($container) }}"
        initial-path="{{ $folder }}"
        initial-editing-asset-id="{{ $editing ?? null }}"
        :can-create-containers="{{ Statamic\Support\Str::bool($user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)) }}"
        create-container-url="{{ cp_route('asset-containers.create') }}"
    ></asset-manager>

    <x-statamic::docs-callout
        topic="{{ __('Assets') }}"
        url="{{ Statamic::docsUrl('assets') }}"
    />
@endsection
