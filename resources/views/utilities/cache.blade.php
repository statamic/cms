@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Cache Manager'))

@section('content')

    <ui-header title="{{ __('Cache Manager') }}" icon="cache">
        <form method="POST" action="{{ cp_route('utilities.cache.clear', 'all') }}">
            @csrf
            <ui-button
                text="{{ __('Clear All') }}"
                type="submit"
                variant="primary"
            />
        </form>
    </ui-header>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <ui-panel class="h-full flex flex-col">
            <ui-panel-header class="flex items-center justify-between min-h-10">
                <ui-heading>{{ __('Content Stache') }}</ui-heading>
                <div class="flex gap-2">
                    <form method="POST" action="{{ cp_route('utilities.cache.warm', 'stache') }}">
                        @csrf
                        <ui-button text="{{ __('Warm') }}" type="submit" size="sm" />
                    </form>
                    <form method="POST" action="{{ cp_route('utilities.cache.clear', 'stache') }}">
                        @csrf
                        <ui-button text="{{ __('Clear') }}" type="submit" size="sm" />
                    </form>
                </div>
            </ui-panel-header>
            <ui-card class="flex-1">
                <ui-description>{{ __('statamic::messages.cache_utility_stache_description') }}</ui-description>
                <div class="flex flex-wrap gap-2 mt-3">
                    <ui-badge :prepend="__('Records')">
                        {{ $stache['records'] }}
                    </ui-badge>
                    @if ($stache['size'])
                        <ui-badge :prepend="__('Size')">
                            {{ $stache['size'] }}
                        </ui-badge>
                    @endif

                    @if ($stache['time'])
                        <ui-badge :prepend="__('Build time')">
                            {{ $stache['time'] }}
                        </ui-badge>
                    @endif

                    @if ($stache['rebuilt'])
                        <ui-badge :prepend="__('Last rebuild')">
                            {{ $stache['rebuilt'] }}
                        </ui-badge>
                    @endif
                </div>
            </ui-card>
        </ui-panel>

        <ui-panel class="h-full flex flex-col">
            <ui-panel-header class="flex items-center justify-between min-h-10">
                <ui-heading>{{ __('Static Page Cache') }}</ui-heading>
                @if ($static['enabled'])
                    <div class="flex gap-2">
                        <form method="POST" action="{{ cp_route('utilities.cache.clear', 'static') }}">
                            @csrf
                            <ui-button text="{{ __('Clear') }}" type="submit" size="sm" />
                        </form>
                    </div>
                @endif
            </ui-panel-header>
            <ui-card class="flex-1">
                <ui-description>{{ __('statamic::messages.cache_utility_static_cache_description') }}</ui-description>
                <div class="flex flex-wrap gap-2 mt-3">
                    <ui-badge :prepend="__('Strategy')">
                        {{ $static['strategy'] }}
                    </ui-badge>
                    @if ($static['enabled'])
                        <ui-badge :prepend="__('Cached Pages')">
                            {{ $static['count'] }}
                        </ui-badge>
                    @endif
                </div>
            </ui-card>
        </ui-panel>

        <ui-panel class="h-full flex flex-col">
            <ui-panel-header class="flex items-center justify-between min-h-10">
                <ui-heading>{{ __('Application Cache') }}</ui-heading>
                <div class="flex gap-2">
                    <form method="POST" action="{{ cp_route('utilities.cache.clear', 'application') }}">
                        @csrf
                        <ui-button text="{{ __('Clear') }}" type="submit" size="sm" />
                    </form>
                </div>
            </ui-panel-header>
            <ui-card class="flex-1">
                <ui-description>{{ __('statamic::messages.cache_utility_application_cache_description') }}</ui-description>
                <div class="flex flex-wrap gap-2 mt-3">
                    <ui-badge :prepend="__('Driver')">
                        {{ $cache['driver'] }}
                    </ui-badge>
                </div>
            </ui-card>
        </ui-panel>

        <ui-panel class="h-full flex flex-col">
            <ui-panel-header class="flex items-center justify-between min-h-10">
                <ui-heading>{{ __('Image Cache') }}</ui-heading>
                <div class="flex gap-2">
                    <form method="POST" action="{{ cp_route('utilities.cache.clear', 'image') }}">
                        @csrf
                        <ui-button text="{{ __('Clear') }}" type="submit" size="sm" />
                    </form>
                </div>
            </ui-panel-header>
            <ui-card class="flex-1">
                <ui-description>{{ __('statamic::messages.cache_utility_image_cache_description') }}</ui-description>
                <div class="flex flex-wrap gap-2 mt-3">
                    <ui-badge :prepend="__('Cached images')">
                        {{ $images['count'] }}
                </ui-badge>
                    <ui-badge :prepend="__('Size')">
                        {{ $images['size'] }}
                    </ui-badge>
                </div>
            </ui-card>
        </ui-panel>
    </div>

    <x-statamic::docs-callout
        topic="{{ __('caching') }}"
        url="{{ Statamic::docsUrl('caching') }}"
    />
@stop
