@extends('statamic::layout')
@section('title', __('Configure User Group'))

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
        :can-edit-password="false"
        :can-edit-blueprint="{{ Statamic\Support\Str::bool($user->can('configure fields')) }}"
    ></user-publish-form>

@endsection
