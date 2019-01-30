@extends('statamic::layout')
@section('title', __('Cache Manager'))

@section('content')

    <div class="flex items-center justify-between">
        <h1>{{ __('Cache Manager') }}</h1>
        <button class="btn-primary">{{ __('Clear All') }}</button>
    </div>

    <div class="mt-3 card p-0">
        <div class="p-2">
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <h2 class="font-bold">{{ __('Content Stache') }}</h2>
                    <p class="text-grey text-sm my-1">The Stache is Statamic's content store that functions much like a database. It is generated automatically from your content files.</p>
                </div>
                <button class="btn">{{ __('Clear') }}</button>
            </div>
            <div class="text-sm text-grey flex">
                <div class="mr-2 badge-pill-sm"><span class="text-grey-dark font-medium">Records:</span> 451</div>
                <div class="mr-2 badge-pill-sm"><span class="text-grey-dark font-medium">Size:</span> 588 kb</div>
                <div class="mr-2 badge-pill-sm"><span class="text-grey-dark font-medium">Build time:</span> 7 seconds</div>
                <div class="badge-pill-sm"><span class="text-grey-dark font-medium">Last rebuild:</span> Jan 30 at 6:45 AM</div>
            </div>
        </div>
        <div class="p-2 bg-grey-lightest border-t">
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <h2 class="font-bold">{{ __('Static Page Cache') }}</h2>
                    <p class="text-grey text-sm my-1">Static pages bypass Statamic completely and are rendered directly from your server for maximum performance.</p>
                </div>
                <button class="btn">{{ __('Clear') }}</button>
            </div>
            <div class="text-sm text-grey flex">
                <div class="mr-2 badge-pill-sm"><span class="text-grey-dark font-medium">Pages:</span> 380</div>
                <div class="mr-2 badge-pill-sm"><span class="text-grey-dark font-medium">Build time:</span> 1 minute 22 seconds</div>
                <div class="badge-pill-sm"><span class="text-grey-dark font-medium">Last updated:</span> Jan 30 at 5:58 AM</div>
            </div>
        </div>

        <div class="p-2 border-t">
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <h2 class="font-bold">{{ __('Application Cache') }}</h2>
                    <p class="text-grey text-sm my-1">Laravel's unified cache used by Statamic, third party addons, and composer packages.</p>
                </div>
                <button class="btn">{{ __('Clear') }}</button>
            </div>
            <div class="text-sm text-grey flex">
                <div class="mr-2 badge-pill-sm"><span class="text-grey-dark font-medium">Driver:</span> File</div>
                <div class="badge-pill-sm"><span class="text-grey-dark font-medium">Last updated:</span> Jan 30 at 5:58 AM</div>
            </div>
        </div>

        <div class="p-2 border-t bg-grey-lightest rounded-b">
            <div class="flex justify-between items-center">
                <div class="pr-4">
                    <h2 class="font-bold">{{ __('Image Cache') }}</h2>
                    <p class="text-grey text-sm my-1">The image cache stores copies of all transformed and resized images.</p>
                </div>
                <button class="btn">{{ __('Clear') }}</button>
            </div>
            <div class="text-sm text-grey flex">
                <div class="mr-2 badge-pill-sm"><span class="text-grey-dark font-medium">Cached images:</span> 3,135</div>
                <div class="badge-pill-sm"><span class="text-grey-dark font-medium">Last updated:</span> Jan 26 at 12:30 PM</div>
            </div>
        </div>
    </div>

@stop

@section('nontent')

    <h1>{{ __('Cache') }}</h1>

    <div class="mt-4 p-3 rounded shadow bg-white">
        <form method="POST" action="{{ cp_route('utilities.cache.clear') }}">
            @csrf
            @if ($errors->has('caches'))
                <p class="mb-1"><small class="help-block text-red">{{ $errors->first() }}</small></p>
            @endif
            <label class="mb-1"><input type="checkbox" name="caches[]" value="cache" class="mr-1">Application Cache</label>
            <label class="mb-1"><input type="checkbox" name="caches[]" value="stache" class="mr-1">Stache Datastore</label>
            <label class="mb-1"><input type="checkbox" name="caches[]" value="static" class="mr-1">Static Page Cache</label>
            <label class="mb-1"><input type="checkbox" name="caches[]" value="glide" class="mr-1">Glide Image Cache</label>
            <button type="submit" class="btn btn-primary mt-1">Clear</button>
        </form>
    </div>

@stop
