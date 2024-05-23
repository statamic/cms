@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Cache Manager'))

@section('content')

    <header class="mb-6">

        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])
        <div class="flex items-center justify-between">
            <h1>{{ __('Cache Manager') }}</h1>

            <form method="POST" action="{{ cp_route('utilities.cache.clear', 'all') }}">
                @csrf
                <button class="btn-primary">{{ __('Clear All') }}</button>
            </form>
        </div>
    </header>

    <div class="card p-0">
        <div class="p-4">
            <div class="flex justify-between items-center">
                <div class="rtl:pl-8 ltr:pr-8">
                    <h2 class="font-bold">{{ __('Content Stache') }}</h2>
                    <p class="text-gray dark:text-dark-150 text-sm my-2">{{ __('statamic::messages.cache_utility_stache_description') }}</p>
                </div>
                <div class="flex">
                    <form method="POST" action="{{ cp_route('utilities.cache.warm', 'stache') }}" class="rtl:ml-2 ltr:mr-2">
                        @csrf
                        <button class="btn">{{ __('Warm') }}</button>
                    </form>
                    <form method="POST" action="{{ cp_route('utilities.cache.clear', 'stache') }}">
                        @csrf
                        <button class="btn">{{ __('Clear') }}</button>
                    </form>
                </div>
            </div>
            <div class="text-sm text-gray dark:text-dark-150 flex">
                <div class="rtl:ml-4 ltr:mr-4 badge-pill-sm"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Records') }}:</span> {{ $stache['records'] }}</div>
                @if($stache['size'])
                    <div class="rtl:ml-4 ltr:mr-4 badge-pill-sm"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Size') }}:</span> {{ $stache['size'] }}</div>
                @endif
                @if ($stache['time'])
                    <div class="rtl:ml-4 ltr:mr-4 badge-pill-sm"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Build time') }}:</span> {{ $stache['time'] }}</div>
                @endif
                @if ($stache['rebuilt'])
                    <div class="badge-pill-sm"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Last rebuild') }}:</span> {{ $stache['rebuilt'] }}</div>
                @endif
            </div>
        </div>
        <div class="p-4 bg-gray-200 dark:bg-dark-700 border-t dark:border-dark-900">
            <div class="flex justify-between items-center">
                <div class="rtl:pl-8 ltr:pr-8">
                    <h2 class="font-bold">{{ __('Static Page Cache') }}</h2>
                    <p class="text-gray dark:text-dark-150 text-sm my-2">{{ __('statamic::messages.cache_utility_static_cache_description') }}</p>
                </div>
                @if ($static['enabled'])
                    <form method="POST" action="{{ cp_route('utilities.cache.clear', 'static') }}">
                        @csrf
                        <button class="btn">{{ __('Clear') }}</button>
                    </form>
                @endunless
            </div>
            <div class="text-sm text-gray dark:text-dark-150 flex">
                <div class="rtl:ml-4 ltr:mr-4 badge-pill-sm bg-white dark:bg-dark-700 border dark:border-dark-900"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Strategy') }}:</span> {{ $static['strategy'] }}</div>
                @if ($static['enabled'])
                    <div class="rtl:ml-4 ltr:mr-4 badge-pill-sm bg-white dark:bg-dark-700 border dark:border-dark-900"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Pages') }}:</span> {{ $static['count'] }}</div>
                @endif
            </div>
        </div>

        <div class="p-4 border-t dark:border-dark-900">
            <div class="flex justify-between items-center">
                <div class="rtl:pl-8 ltr:pr-8">
                    <h2 class="font-bold">{{ __('Application Cache') }}</h2>
                    <p class="text-gray dark:text-dark-150 text-sm my-2">{{ __('statamic::messages.cache_utility_application_cache_description') }}</p>
                </div>
                <form method="POST" action="{{ cp_route('utilities.cache.clear', 'application') }}">
                    @csrf
                    <button class="btn">{{ __('Clear') }}</button>
                </form>
            </div>
            <div class="text-sm text-gray dark:text-dark-150 flex">
                <div class="rtl:ml-4 ltr:mr-4 badge-pill-sm"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Driver') }}:</span> {{ $cache['driver'] }}</div>
            </div>
        </div>

        <div class="p-4 border-t dark:border-dark-900 bg-gray-200 dark:bg-dark-700 rounded-b">
            <div class="flex justify-between items-center">
                <div class="rtl:pl-8 ltr:pr-8">
                    <h2 class="font-bold">{{ __('Image Cache') }}</h2>
                    <p class="text-gray dark:text-dark-150 text-sm my-2">{{ __('statamic::messages.cache_utility_image_cache_description') }}</p>
                </div>
                <form method="POST" action="{{ cp_route('utilities.cache.clear', 'image') }}">
                    @csrf
                    <button class="btn">{{ __('Clear') }}</button>
                </form>
            </div>
            <div class="text-sm text-gray dark:text-dark-150 flex">
                <div class="rtl:ml-4 ltr:mr-4 badge-pill-sm bg-white dark:bg-dark-700 border dark:border-dark-900"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Cached images') }}:</span> {{ $images['count'] }}</div>
                <div class="rtl:ml-4 ltr:mr-4 badge-pill-sm bg-white dark:bg-dark-700 border dark:border-dark-900"><span class="text-gray-800 dark:text-dark-150 font-medium">{{ __('Size') }}:</span> {{ $images['size'] }}</div>
            </div>
        </div>
    </div>

@stop
