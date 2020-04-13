@extends('statamic::layout')
@section('title', __('Assets'))

@section('content')

    @include('statamic::partials.empty-state', [
        'resource' => 'Asset Container',
        'title' => __('Create an Asset Container'),
        'description' => __('statamic::messages.asset_container_intro'),
        'svg' => 'empty/asset-container',
        'route' => cp_route('asset-containers.create'),
        'can' => $user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)
    ])

    @include('statamic::partials.docs-callout', [
        'topic' => __('Assets'),
        'url' => 'assets'
    ])

@endsection
