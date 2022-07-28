@extends('statamic::layout')
@section('title', __('Asset Containers'))

@section('content')

    <header class="mb-3">
        <div class="flex flex-wrap items-center max-w-full gap-2">
            <h1 class="flex-1 break-words max-w-full">{{ __('Asset Containers') }}</h1>

            @can('create', 'Statamic\Contracts\Assets\AssetContainer')
                <a href="{{ cp_route('asset-containers.create') }}" class="btn">{{ __('Create Asset Container') }}</a>
            @endcan
        </div>
    </header>

    <asset-container-list
        :initial-rows="{{ json_encode($containers) }}"
        :columns="{{ json_encode($columns) }}"
        :visible-columns="{{ json_encode($visibleColumns) }}"
    ></asset-container-list>

@endsection
