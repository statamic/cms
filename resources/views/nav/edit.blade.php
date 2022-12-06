@extends('statamic::layout')

@section('content')

<nav-builder
    title="{{ $title }}"
    index-url="{{ Statamic\Facades\User::current()->isSuper() ? cp_route('preferences.nav.index') : false }}"
    update-url="{{ $updateUrl }}"
    :current-nav="{{ json_encode($currentNav) }}"
    :default-nav="{{ json_encode($defaultNav) }}"
></nav-builder>

@endsection
