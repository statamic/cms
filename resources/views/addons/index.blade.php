@extends('statamic::layout')
@section('title', __('Addons'))

@section('content')

    <addon-list :install-count="{{ $addonCount }}"></addon-list>

@endsection
