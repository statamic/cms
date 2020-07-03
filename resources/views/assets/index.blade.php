@extends('statamic::layout')
@section('title', __('Assets'))

@section('content')

    @include('statamic::partials.empty-state', [
        'title' => __('Asset Containers'),
        'description' => __('statamic::messages.asset_container_intro'),
        'svg' => 'empty/asset-container',
        'button_text' => __('Create Asset Container'),
        'button_url' => cp_route('asset-containers.create'),
        'can' => $user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)
    ])

    @include('statamic::partials.docs-callout', [
        'topic' => __('Assets'),
        'url' => 'assets'
    ])

@endsection
