@extends('statamic::layout')
@section('title', crumb('Assets', $container['title']))

@section('content')

    <asset-manager
        :initial-container="{{ json_encode($container) }}"
        initial-path="{{ $folder }}"
        action-url="{{ cp_route('assets.actions') }}"
        folder-action-url="{{ cp_route('assets.folders.actions', $container['id']) }}"
        :can-create-containers="{{ bool_str(user()->can('create', \Statamic\Contracts\Assets\AssetContainer::class)) }}"
        create-container-url="{{ cp_route('asset-containers.create') }}"
    ></asset-manager>

@endsection
