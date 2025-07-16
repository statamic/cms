@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Asset Containers'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <header class="py-8 mt-8 text-center">
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
            <ui-icon name="assets" class="size-5 text-gray-500"></ui-icon>
            {{ __('Asset Containers') }}
        </h1>
    </header>

    <ui-empty-state-menu heading="{{ __('statamic::messages.asset_container_intro') }}">
        <ui-empty-state-item
            href="{{ cp_route('asset-containers.create') }}"
            icon="assets"
            heading="{{ __('Create Asset Container') }}"
            description="{{ __('Get started by creating your first asset container.') }}"
        ></ui-empty-state-item>
    </ui-empty-state-menu>

    <x-statamic::docs-callout
        topic="{{ __('Assets') }}"
        url="{{ Statamic::docsUrl('assets') }}"
    />
@endsection
