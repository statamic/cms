@extends('statamic::layout')

@section('content')

    <asset-manager
        :initial-container="{{ json_encode($container) }}"
        initial-path="{{ $folder }}"
        :actions="{{ $actions->toJson() }}"
        action-url="{{ cp_route('assets.action') }}"
    ></asset-manager>

@endsection
