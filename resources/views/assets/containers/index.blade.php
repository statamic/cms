@extends('statamic::layout')
@section('title', Statamic\trans('Asset Containers'))

@section('content')

    <div class="flex mb-6">
        <h1 class="flex-1">{{ Statamic\trans('Asset Containers') }}</h1>

        @can('create', 'Statamic\Contracts\Assets\AssetContainer')
            <a href="{{ cp_route('asset-containers.create') }}" class="btn">{{ Statamic\trans('Create Asset Container') }}</a>
        @endcan
    </div>

    <asset-container-list
        :initial-rows="{{ json_encode($containers) }}"
        :columns="{{ json_encode($columns) }}"
        :visible-columns="{{ json_encode($visibleColumns) }}"
    ></asset-container-list>

@endsection
