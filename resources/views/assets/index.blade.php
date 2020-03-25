@extends('statamic::layout')
@section('title', __('Assets'))

@section('content')

    @include('statamic::partials.empty-state', [
        'resource' => 'Asset Container',
        'title' => __('Create an Asset Container'),
        'description' => __('statamic::messages.asset_container_intro'),
        'docs_link' => Statamic::docsUrl('assets#containers'),
        'svg' => 'empty/asset-container',
        'route' => cp_route('asset-containers.create'),
        'can' => $user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)
    ])

@endsection
