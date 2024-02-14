@extends('statamic::layout')
@section('title', Statamic\trans('Assets'))

@section('content')

    @include('statamic::partials.empty-state', [
        'title' => Statamic\trans('Asset Containers'),
        'description' => Statamic\trans('statamic::messages.asset_container_intro'),
        'svg' => 'empty/asset-container',
        'button_text' => Statamic\trans('Create Asset Container'),
        'button_url' => cp_route('asset-containers.create'),
        'can' => $user->can('create', \Statamic\Contracts\Assets\AssetContainer::class)
    ])

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Assets'),
        'url' => 'assets'
    ])

@endsection
