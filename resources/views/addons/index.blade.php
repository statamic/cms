@extends('statamic::layout')
@section('title', Statamic\trans('Addons'))

@section('content')

    <addon-list :install-count="{{ $addonCount }}"></addon-list>

@endsection
