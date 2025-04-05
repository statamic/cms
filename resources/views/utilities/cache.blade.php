@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Cache Manager'))

@section('content')

    <ui-header title="{{ __('Cache Manager') }}">
        <form method="POST" action="{{ cp_route('utilities.cache.clear', 'all') }}">
            @csrf
            <ui-button
                text="{{ __('Clear All') }}"
                type="submit"
                variant="primary"
            />
        </form>
    </ui-header>
</header>

<ui-card-panel>
    <div class="p-4">
        <div class="flex items-center justify-between">
            <div class="ltr:pr-8 rtl:pl-8">
                <h2 class="font-bold">{{ __('Content Stache') }}</h2>
                <p class="my-2 text-sm text-gray dark:text-dark-150">
                    {{ __('statamic::messages.cache_utility_stache_description') }}
                </p>
            </div>
            <div class="flex">
                <form
                    method="POST"
                    action="{{ cp_route('utilities.cache.warm', 'stache') }}"
                    class="ltr:mr-2 rtl:ml-2"
                >
                    @csrf
                    <button class="btn">{{ __('Warm') }}</button>
                </form>
                <form method="POST" action="{{ cp_route('utilities.cache.clear', 'stache') }}">
                    @csrf
                    <button class="btn">{{ __('Clear') }}</button>
                </form>
            </div>
        </div>
        <div class="flex text-sm text-gray dark:text-dark-150">
            <div class="badge-pill-sm ltr:mr-4 rtl:ml-4">
                <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Records') }}:</span>
                {{ $stache['records'] }}
            </div>
            @if ($stache['size'])
                <div class="badge-pill-sm ltr:mr-4 rtl:ml-4">
                    <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Size') }}:</span>
                    {{ $stache['size'] }}
                </div>
            @endif

            @if ($stache['time'])
                <div class="badge-pill-sm ltr:mr-4 rtl:ml-4">
                    <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Build time') }}:</span>
                    {{ $stache['time'] }}
                </div>
            @endif

            @if ($stache['rebuilt'])
                <div class="badge-pill-sm">
                    <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Last rebuild') }}:</span>
                    {{ $stache['rebuilt'] }}
                </div>
            @endif
        </div>
    </div>
    <div class="border-t bg-gray-100 p-4 dark:border-dark-900 dark:bg-dark-700">
        <div class="flex items-center justify-between">
            <div class="ltr:pr-8 rtl:pl-8">
                <h2 class="font-bold">{{ __('Static Page Cache') }}</h2>
                <p class="my-2 text-sm text-gray dark:text-dark-150">
                    {{ __('statamic::messages.cache_utility_static_cache_description') }}
                </p>
            </div>
            @if ($static['enabled'])
                <form method="POST" action="{{ cp_route('utilities.cache.clear', 'static') }}">
                    @csrf
                    <button class="btn">{{ __('Clear') }}</button>
                </form>
            @endif
        </div>
        <div class="flex text-sm text-gray dark:text-dark-150">
            <div class="badge-pill-sm border bg-white dark:border-dark-900 dark:bg-dark-700 ltr:mr-4 rtl:ml-4">
                <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Strategy') }}:</span>
                {{ $static['strategy'] }}
            </div>
            @if ($static['enabled'])
                <div class="badge-pill-sm border bg-white dark:border-dark-900 dark:bg-dark-700 ltr:mr-4 rtl:ml-4">
                    <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Pages') }}:</span>
                    {{ $static['count'] }}
                </div>
            @endif
        </div>
    </div>

    <div class="border-t p-4 dark:border-dark-900">
        <div class="flex items-center justify-between">
            <div class="ltr:pr-8 rtl:pl-8">
                <h2 class="font-bold">{{ __('Application Cache') }}</h2>
                <p class="my-2 text-sm text-gray dark:text-dark-150">
                    {{ __('statamic::messages.cache_utility_application_cache_description') }}
                </p>
            </div>
            <form method="POST" action="{{ cp_route('utilities.cache.clear', 'application') }}">
                @csrf
                <button class="btn">{{ __('Clear') }}</button>
            </form>
        </div>
        <div class="flex text-sm text-gray dark:text-dark-150">
            <div class="badge-pill-sm ltr:mr-4 rtl:ml-4">
                <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Driver') }}:</span>
                {{ $cache['driver'] }}
            </div>
        </div>
    </div>

    <div class="rounded-b border-t bg-gray-100 p-4 dark:border-dark-900 dark:bg-dark-700">
        <div class="flex items-center justify-between">
            <div class="ltr:pr-8 rtl:pl-8">
                <h2 class="font-bold">{{ __('Image Cache') }}</h2>
                <p class="my-2 text-sm text-gray dark:text-dark-150">
                    {{ __('statamic::messages.cache_utility_image_cache_description') }}
                </p>
            </div>
            <form method="POST" action="{{ cp_route('utilities.cache.clear', 'image') }}">
                @csrf
                <button class="btn">{{ __('Clear') }}</button>
            </form>
        </div>
        <div class="flex text-sm text-gray dark:text-dark-150">
            <div class="badge-pill-sm border bg-white dark:border-dark-900 dark:bg-dark-700 ltr:mr-4 rtl:ml-4">
                <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Cached images') }}:</span>
                {{ $images['count'] }}
            </div>
            <div class="badge-pill-sm border bg-white dark:border-dark-900 dark:bg-dark-700 ltr:mr-4 rtl:ml-4">
                <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Size') }}:</span>
                {{ $images['size'] }}
            </div>
        </div>
    </div>
</ui-card-panel>

@stop
