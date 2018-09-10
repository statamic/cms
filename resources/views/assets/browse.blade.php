@extends('statamic::layout')

@section('content')

    <asset-manager
        :initial-container="{{ json_encode($container) }}"
        initial-path="{{ $folder }}"
    ></asset-manager>

@endsection
