@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Assets', $container['title']))

@section('content')
    <asset-manager
        :container="{{ json_encode($container) }}"
        :can-create-containers="{{ Statamic\Support\Str::bool($user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)) }}"
        create-container-url="{{ cp_route('asset-containers.create') }}"
        initial-path="{{ $folder }}"
        initial-editing-asset-id="{{ $editing ?? null }}"
        :columns="{{ $columns->toJson() }}"
    ></asset-manager>

    <x-statamic::docs-callout topic="{{ __('Assets') }}" url="{{ Statamic::docsUrl('assets') }}" />
@endsection
