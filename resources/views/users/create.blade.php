@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', __('Create User'))

@section('content')
    <user-wizard
        route="{{ cp_route('users.store') }}"
        users-index-url="{{ cp_route('users.index') }}"
        users-create-url="{{ cp_route('users.create') }}"
        :can-create-supers="{{ $str::bool($user->can('super')) }}"
        :activation-expiry="{{ $expiry }}"
        :separate-name-fields="{{ $str::bool($separateNameFields) }}"
    >
    </user-wizard>
@stop
