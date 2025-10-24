@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Edit User'))

@section('content')
    <user-publish-form
        :actions="{{ json_encode($actions) }}"
        method="patch"
        publish-container="base"
        initial-title="{{ $title }}"
        initial-reference="{{ $reference }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :can-edit-password="{{ Statamic\Support\Str::bool($canEditPassword) }}"
        :can-edit-blueprint="{{ Statamic\Support\Str::bool($user->can('configure fields')) }}"
        :requires-current-password="{{ Statamic\Support\Str::bool($requiresCurrentPassword) }}"
        :initial-item-actions="{{ json_encode($itemActions) }}"
        item-action-url="{{ cp_route('users.actions.run') }}"
        :two-factor="{{ json_encode($twoFactor) }}"
    ></user-publish-form>
@endsection
