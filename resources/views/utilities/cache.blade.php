@extends('statamic::layout')
@section('title', __('Cache Manager'))

@section('content')

    <div class="flex items-center justify-between">
        <h1>{{ __('Cache Manager') }}</h1>

        <form method="POST" action="{{ cp_route('utilities.cache.clear', 'all') }}">
            @csrf
            <button class="btn-primary">{{ __('Clear All') }}</button>
        </form>
    </div>

    <div class="mt-3 card p-0">
        <div class="p-2">
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <h2 class="font-bold">{{ __('Content Stache') }}</h2>
                    <p class="text-grey text-sm my-1">The Stache is Statamic's content store that functions much like a database. It is generated automatically from your content files.</p>
                </div>
                <form method="POST" action="{{ cp_route('utilities.cache.clear', 'stache') }}">
                    @csrf
                    <button class="btn">{{ __('Clear') }}</button>
                </form>
            </div>
            <div class="text-sm text-grey flex">
                <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">Records:</span> {{ $stache['records'] }}</div>
                <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">Size:</span> {{ $stache['size'] }}</div>
                @if ($stache['time'])
                    <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">Build time:</span> {{ $stache['time'] }}</div>
                @endif
                @if ($stache['rebuilt'])
                    <div class="badge-pill-sm"><span class="text-grey-80 font-medium">Last rebuild:</span> {{ $stache['rebuilt'] }}</div>
                @endif
            </div>
        </div>
        <div class="p-2 bg-grey-20 border-t">
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <h2 class="font-bold">{{ __('Static Page Cache') }}</h2>
                    <p class="text-grey text-sm my-1">Static pages bypass Statamic completely and are rendered directly from your server for maximum performance.</p>
                </div>
                @if ($static['enabled'])
                    <form method="POST" action="{{ cp_route('utilities.cache.clear', 'static') }}">
                        @csrf
                        <button class="btn">{{ __('Clear') }}</button>
                    </form>
                @endunless
            </div>
            <div class="text-sm text-grey flex">
                <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">Strategy:</span> {{ $static['strategy'] }}</div>
                @if ($static['enabled'])
                    <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">Pages:</span> {{ $static['count'] }}</div>
                @endif
            </div>
        </div>

        <div class="p-2 border-t">
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <h2 class="font-bold">{{ __('Application Cache') }}</h2>
                    <p class="text-grey text-sm my-1">Laravel's unified cache used by Statamic, third party addons, and composer packages.</p>
                </div>
                <form method="POST" action="{{ cp_route('utilities.cache.clear', 'application') }}">
                    @csrf
                    <button class="btn">{{ __('Clear') }}</button>
                </form>
            </div>
            <div class="text-sm text-grey flex">
                <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">Driver:</span> {{ $cache['driver'] }}</div>
            </div>
        </div>

        <div class="p-2 border-t bg-grey-20 rounded-b">
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <h2 class="font-bold">{{ __('Image Cache') }}</h2>
                    <p class="text-grey text-sm my-1">The image cache stores copies of all transformed and resized images.</p>
                </div>
                <form method="POST" action="{{ cp_route('utilities.cache.clear', 'image') }}">
                    @csrf
                    <button class="btn">{{ __('Clear') }}</button>
                </form>
            </div>
            <div class="text-sm text-grey flex">
                <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">Cached images:</span> {{ $images['count'] }}</div>
                <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">Size:</span> {{ $images['size'] }}</div>
            </div>
        </div>
    </div>

@stop
