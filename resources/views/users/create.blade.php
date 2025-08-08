@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', __('Create User'))
@section('content-card-modifiers', 'bg-architectural-lines')

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
    :fields="{{ json_encode($fields) }}"
    :meta="{{ json_encode($meta) }}"
    :initial-values="{{ json_encode($values) }}"
></user-wizard>
@stop
