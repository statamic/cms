@extends('statamic::layout')

@section('content')

<nav-builder
    title="{{ $title }}"
    index-url="{{ Statamic\Statamic::pro() && Statamic\Facades\User::current()->can('manage preferences') ? cp_route('preferences.nav.index') : false }}"
    update-url="{{ $updateUrl }}"
    destroy-url="{{ $destroyUrl }}"
    :save-as-options="{{ json_encode($saveAsOptions) }}"
    :nav="{{ json_encode($nav) }}"
></nav-builder>

@endsection
