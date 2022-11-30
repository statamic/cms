@extends('statamic::layout')

@section('content')

<nav-builder
    :current-nav="{{ json_encode($currentNav) }}"
    :default-nav="{{ json_encode($defaultNav) }}"
    :roles="{{ json_encode($roles ?? []) }}"
></nav-builder>

@endsection
