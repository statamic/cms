@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Assets'))

@section('content')
    <x-statamic::empty-screen
        title="{{ __('Asset Containers') }}"
        description="{{ __('statamic::messages.asset_container_intro') }}"
        svg="empty/asset-container"
        button-text="{{ __('Create Asset Container') }}"
        button-url="{{ cp_route('asset-containers.create') }}"
        :can="$user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)"
    />

    <x-statamic::docs-callout topic="{{ __('Assets') }}" url="{{ Statamic::docsUrl('assets') }}" />
@endsection
