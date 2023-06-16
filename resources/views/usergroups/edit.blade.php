@extends('statamic::layout')
@section('title', __('Configure User Group'))

@section('content')

    <user-group-publish-form
        :actions="{{ json_encode($actions) }}"
        method="patch"
        publish-container="base"
        initial-title="{{ $title }}"
        initial-reference="{{ $reference }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :can-edit-blueprint="{{ Statamic\Support\Str::bool($user->can('configure fields')) }}"
        breadcrumb-url="{{ cp_route('user-groups.show', $group->handle()) }}"
    ></user-group-publish-form>

@endsection
