@extends('statamic::layout')
@section('title', __('Assets'))

@section('content')

    @include('statamic::partials.create-first', [
        'resource' => 'Asset Container',
        'description' => 'Asset containers are the "source" where a set of assets and folders are located.',
        'svg' => 'empty/collection', // TODO: Need empty/asset-container svg
        'route' => cp_route('asset-containers.create'),
        'can' => $user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)
    ])

@endsection
