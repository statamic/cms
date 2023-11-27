@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', __('Create User'))

@section('content')
    <user-wizard
        route="{{ cp_route('users.store') }}"
        users-index-url="{{ cp_route('users.index') }}"
        users-create-url="{{ cp_route('users.create') }}"
        :can-create-supers="{{ $str::bool($user->isSuper()) }}"
        :can-assign-roles="{{ $str::bool($user->can('assign roles')) }}"
        :can-assign-groups="{{ $str::bool($user->can('assign user groups')) }}"
        :activation-expiry="{{ $expiry }}"
        :separate-name-fields="{{ $str::bool($separateNameFields) }}"
        :can-send-invitation="{{ $str::bool($canSendInvitation) }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :additional-fields="{{ json_encode($fields) }}"
        :additional-meta="{{ json_encode($meta) }}"
        :initial-additional-values="{{ json_encode($values) }}"
    >
    </user-wizard>
@stop
