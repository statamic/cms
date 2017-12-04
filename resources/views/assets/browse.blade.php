@extends('layout')

@section('content')

    <asset-manager
        container="{{ $container }}"
        path="{{ $folder }}">
    </asset-manager>

@endsection
