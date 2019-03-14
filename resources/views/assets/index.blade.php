@extends('statamic::layout')
@section('title', __('Assets'))

@section('content')

    <div class="text-center max-w-md mx-auto mt-5 screen-centered border-2 border-dashed rounded-lg px-4 py-8">
        @svg('empty/collection') {{-- TODO: need empty/asset-container svg --}}
        <h1 class="my-3">{{ __('Create your first Assets Container now') }}</h1>
        <p class="text-grey mb-3">
            {{ __('Asset containers are the "source" where a set of assets and folders are located.') }}
        </p>
        @can('super')
            <a href="{{ cp_route('asset-containers.create') }}" class="btn-primary btn-lg">{{ __('Create Container') }}</a>
        @endcan
    </div>

@endsection
